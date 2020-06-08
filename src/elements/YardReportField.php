<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 06/06/2020
 */

namespace twentyfourhoursmedia\yardreports\elements;

use Craft;
use craft\base\Element;
use craft\base\Field;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use twentyfourhoursmedia\yardreports\helpers\traits\ElementBufferTrait;
use twentyfourhoursmedia\yardreports\elements\db\YardReportFieldQuery;


class YardReportField extends Element
{

    /**
     * @var int
     */
    public $yardReportId;

    /**
     * @var int
     */
    public $structureId;

    /**
     * @var int
     */
    public $fieldId;

    /**
     * @var string
     */
    public $columnName;

    /**
     * @var string
     */
    public $transformerHandle;

    use ElementBufferTrait;

    /**
     * @return YardReport | null
     */
    public function getYardReport()
    {
        return $this->getElementFromStaticBuffer(YardReport::class, $this->yardReportId);
    }


    /**
     * @return \craft\base\FieldInterface|null
     */
    public function getField()
    {
        return $this->getFromStaticBuffer(Field::class, $this->fieldId, static function($id) {
            return Craft::$app->fields->getFieldById($id);
        });
    }


    /**
     * Name of the source field if defined
     * @return |null
     */
    public function getSourceFieldName() {
        $field = $this->getField();
        return $field ? $field->name : null;
    }

    /**
     * The field name to use in reports
     * = the custom name if defined, otherwise the source field name.
     *
     * @return string
     */
    public function getReportFieldName() {
        if ('' !== (string)$this->columnName) {
            return $this->columnName;
        }
        $field = $this->getField();
        return $field->name ?? $this->id;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Yard Report Field';
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return 'Yard Report Fields';
    }

    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            \Craft::$app->db->createCommand()
                ->insert('{{%yardreport_fields}}', [
                    'id' => $this->id,
                    'yardReportId' => $this->yardReportId,
                    'structureId' => $this->structureId,
                    'fieldId' => $this->fieldId,
                    'columnName' => $this->columnName,
                    'transformerHandle' => $this->transformerHandle
                ])
                ->execute();
            \Craft::$app->getStructures()->appendToRoot($this->structureId, $this);
        } else {
            \Craft::$app->db->createCommand()
                ->update('{{%yardreport_fields}}', [
                    'yardReportId' => $this->yardReportId,
                    'structureId' => $this->structureId,
                    'fieldId' => $this->fieldId,
                    'columnName' => $this->columnName,
                    'transformerHandle' => $this->transformerHandle
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * @return YardReportFieldQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new YardReportFieldQuery(static::class);
    }

    public static function hasContent(): bool
    {
        return false;
    }

    public static function hasTitles(): bool
    {
        return false;
    }

    public function getEditorHtml(): string
    {
        return '';
    }

    public static function isLocalized(): bool
    {
        return false;
    }

    protected static function defineSources(string $context = null): array
    {

        $reports = YardReport::find()->all();

        $sources = [];
        foreach ($reports as $report) {
            $sources[] = [
                'key' => 'report' . $report->id,
                'label' => $report->title,
                'data' => ['yardReportId' =>  $report->id],
                'criteria' => [
                    'yardReportId' => $report->id
                ],
                'structureId' => $report->structureId,
                'structureEditable' => true
            ];
        }
        return $sources;
    }

    public static function sources(string $context = null): array
    {
        return parent::sources($context);
    }

    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'yardReportTitle':
                $report = $this->getYardReport();
                return htmlspecialchars($report ? $report->title : '#N/A');
            case 'sourceFieldName':
                $field = $this->getField();
                return htmlspecialchars($field ? $field->name : '#N/A');
        }
        return parent::tableAttributeHtml($attribute);
    }

    /**
     * Also used in lists...
     * @return string
     */
    public function __toString()
    {
        return $this->getReportFieldName();
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'columnName' => 'Report column name',
            'sourceFieldName' => 'Source field',
            'transformerHandle' => 'Transformation',
            'yardReportTitle' => 'Yard report',
        ];
    }

    protected static function defineSortOptions(): array
    {
        return [
           // 'columnName' => \Craft::t('yard-reports', 'Column name'),
        ];
    }

    protected static function defineActions(string $source = null): array
    {
        $elementsService = Craft::$app->getElements();
        $actions = [];

        $actions[] = $elementsService->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('yard-reports', 'Are you sure you want to delete the selected fields?'),
            'successMessage' => Craft::t('yard-reports', 'Fields deleted.'),
        ]);
        return $actions;
    }

    public function getIsEditable(): bool
    {
        return true;
    }

    public function getCpEditUrl()
    {
        return UrlHelper::url('yard-reports/reports/' . (int)$this->yardReportId . '/report-fields/' . (int)$this->id . '/edit');
    }

}