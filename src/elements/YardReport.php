<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 05/06/2020
 */

namespace twentyfourhoursmedia\yardreports\elements;

use barrelstrength\sproutforms\elements\Form as SproutForm;
use Craft;
use craft\base\Element;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use craft\models\Structure;
use twentyfourhoursmedia\yardreports\elements\actions\AddSproutFieldsAction;
use twentyfourhoursmedia\yardreports\elements\db\YardReportQuery;
use twentyfourhoursmedia\yardreports\helpers\traits\ElementBufferTrait;
use twentyfourhoursmedia\yardreports\models\CsvReportOptions;

/**
 * @property FieldLayout|null $fieldLayout           The field layout used by this element
 * @property array $htmlAttributes        Any attributes that should be included in the element’s DOM representation in the Control Panel
 * @property int[] $supportedSiteIds      The site IDs this element is available in
 * @property string|null $uriFormat             The URI format used to generate this element’s URL
 * @property string|null $url                   The element’s full URL
 * @property \Twig_Markup|null $link                  An anchor pre-filled with this element’s URL and title
 * @property string|null $ref                   The reference string to this element
 * @property string $indexHtml             The element index HTML
 * @property bool $isEditable            Whether the current user can edit the element
 * @property string|null $cpEditUrl             The element’s CP edit URL
 * @property string|null $thumbUrl              The URL to the element’s thumbnail, if there is one
 * @property string|null $iconUrl               The URL to the element’s icon image, if there is one
 * @property string|null $status                The element’s status
 * @property Element $next                  The next element relative to this one, from a given set of criteria
 * @property Element $prev                  The previous element relative to this one, from a given set of criteria
 * @property Element $parent                The element’s parent
 * @property mixed $route                 The route that should be used when the element’s URI is requested
 * @property int|null $structureId           The ID of the structure that the element is associated with, if any
 * @property ElementQueryInterface $ancestors             The element’s ancestors
 * @property ElementQueryInterface $descendants           The element’s descendants
 * @property ElementQueryInterface $children              The element’s children
 * @property ElementQueryInterface $siblings              All of the element’s siblings
 * @property Element $prevSibling           The element’s previous sibling
 * @property Element $nextSibling           The element’s next sibling
 * @property bool $hasDescendants        Whether the element has descendants
 * @property int $totalDescendants      The total number of descendants that the element has
 * @property string $title                 The element’s title
 * @property string|null $serializedFieldValues Array of the element’s serialized custom field values, indexed by their handles
 * @property array $fieldParamNamespace   The namespace used by custom field params on the request
 * @property string $contentTable          The name of the table this element’s content is stored in
 * @property string $fieldColumnPrefix     The field column prefix this element’s content uses
 * @property string $fieldContext          The field context this element’s content uses
 *
 * http://pixelandtonic.com/blog/craft-element-types
 *
 * @since     1.0.0
 */
class YardReport extends Element
{

    use ElementBufferTrait;

    /**
     * @var null
     */
    public $sproutFormId;

    /**
     * @var int
     */
    public $structureId;

    /**
     * @var array
     */
    public $csvOptionsJson = '{}';

    /**
     * @return SproutForm|null
     */
    public function getSproutForm()
    {
        return $this->getElementFromStaticBuffer(SproutForm::class, $this->sproutFormId);
        //return SproutForm::find()->id($this->sproutFormId)->one();
    }

    /**
     * @return YardReportField[]
     */
    public function getYardFieldsQuery() : ElementQueryInterface {
        return YardReportField::find()->yardReportId($this->id);
    }

    /**
     * @return YardReportField[]
     */
    public function getYardFields() : array {
        return YardReportField::find()->yardReportId($this->id)->all();
    }

    public function getCsvOptions(CsvReportOptions $default = null) : CsvReportOptions {
        if (!$default) {
            $default = new CsvReportOptions();
        }
        $storedOptions = json_decode($this->csvOptionsJson, true) ?? [];

        $new = new CsvReportOptions();
        $new->setValuesFromArray($storedOptions)->fixTypes();
        return $new;
    }

    /**
     * Set the options in a serialized state in the report element
     *
     * @param CsvReportOptions $options
     * @return $this
     */
    public function setCsvOptionsToPersist(CsvReportOptions $options) : YardReport
    {
        $this->csvOptionsJson = json_encode($options);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Yard Report';
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return 'Yard Reports';
    }

    public function beforeSave(bool $isNew): bool
    {
        // this is a structure
        if ($isNew) {
            $structure = new Structure();
            $structure->maxLevels = 1;
            Craft::$app->getStructures()->saveStructure($structure);
            $this->structureId = $structure->id;
        }

        return true;
    }


    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            \Craft::$app->db->createCommand()
                ->insert('{{%yardreports}}', [
                    'id' => $this->id,
                    'sproutFormId' => $this->sproutFormId,
                    'structureId' => $this->structureId,
                    'csvOptionsJson' => $this->csvOptionsJson
                ])
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update('{{%yardreports}}', [
                    'sproutFormId' => $this->sproutFormId,
                    'structureId' => $this->structureId,
                    'csvOptionsJson' => $this->csvOptionsJson
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    public static function find(): ElementQueryInterface
    {
        return new YardReportQuery(static::class);
    }

    public static function hasContent(): bool
    {
        return true;
    }

    public static function hasTitles(): bool
    {
        return true;
    }

    public function getEditorHtml(): string
    {
        $html = \Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label' => \Craft::t('app', 'Title'),
                'siteId' => $this->siteId,
                'id' => 'title',
                'name' => 'title',
                'value' => $this->title,
                'errors' => $this->getErrors('title'),
                'first' => true,
                'autofocus' => true,
                'required' => true
            ]
        ]);

        // ...

        $html .= parent::getEditorHtml();

        return $html;
    }

    public static function isLocalized(): bool
    {
        return false;
    }

    protected static function defineSources(string $context = null): array
    {
        return [
            [
                'key' => '*',
                'label' => 'All Yard Reports',
                'criteria' => []
            ]
        ];
    }

    public static function sources(string $context = null): array
    {
        return parent::sources($context); // TODO: Change the autogenerated stub
    }

    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'numFields':
                return $this->getYardFieldsQuery()->count();

            case 'actions':
                $html = '<div style="white-space: nowrap; text-align: right;">';

                $text = Craft::t('yard-reports', 'edit fields');
                $url = UrlHelper::url('yard-reports/reports/' . (int)$this->id) . '/report-fields';
                $html.= '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($text) . '</a>';

                $html.= ' | ';

                $url = UrlHelper::url('yard-reports/reports/' . (int)$this->id) . '/download';
                $text = Craft::t('yard-reports', 'download');
                $html.= '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($text) . '</a>';

                $html.= ' | ';

                $url = UrlHelper::url('yard-reports/reports/' . (int)$this->id) . '/preview';
                $text = Craft::t('yard-reports', 'preview');
                $html.= '<a target="_blank" href="' . htmlspecialchars($url) . '">' . htmlspecialchars($text) . '</a>';

                $html.= '</span>';
                return $html;
        }

        return parent::tableAttributeHtml($attribute);
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'title' => \Craft::t('app', 'Title'),
            'sproutForm' => 'Sprout form',
            'numFields' => 'Number of fields',
            'actions' => '',
        ];
    }

    protected static function defineSortOptions(): array
    {
        return [
            'title' => \Craft::t('app', 'Title'),
        ];
    }

    protected static function defineActions(string $source = null): array
    {
        $elementsService = Craft::$app->getElements();
        $actions = [];

        $actions[] = $elementsService->createAction([
            'type' => AddSproutFieldsAction::class,
            'label' => 'Add fields from the sprout form',

        ]);
        $actions[] = $elementsService->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('yard-reports', 'Are you sure you want to delete the selected reports?'),
            'successMessage' => Craft::t('yard-reports', 'Reports deleted.'),
        ]);

        return $actions;
    }

    public function getIsEditable(): bool
    {
        return true;
        //return \Craft::$app->user->checkPermission('edit-yardreport:'.$this->getType()->id);
    }

    public function getCpEditUrl()
    {
        return UrlHelper::url('yard-reports/reports/' . (int)$this->id) . '/edit';
    }

}