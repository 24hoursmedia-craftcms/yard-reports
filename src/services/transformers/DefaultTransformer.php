<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 07/06/2020
 */
namespace twentyfourhoursmedia\yardreports\services\transformers;

use barrelstrength\sproutbasefields\models as SproutModels;
use barrelstrength\sproutbasefields\SproutBaseFields;
use barrelstrength\sproutforms\fields\formfields as SproutFields;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\db\AssetQuery;
use craft\elements\db\CategoryQuery;
use craft\elements\db\EntryQuery;
use craft\elements\db\TagQuery;
use craft\elements\db\UserQuery;
use craft\elements\Entry;
use craft\elements\Tag;
use craft\elements\User;
use craft\fields\data\MultiOptionsFieldData;
use craft\fields\data\OptionData;
use craft\fields\data\SingleOptionFieldData;
use twentyfourhoursmedia\yardreports\models\CsvReportOptions;
use twentyfourhoursmedia\yardreports\services\transformers\TransformerInterface;

class DefaultTransformer implements TransformerInterface
{

    /**
     * @inheritDoc
     */
    public static function getHandle(): string
    {
        return 'default';
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'default transformer';
    }

    /**
     * @inheritDoc
     */
    public function supports(FieldInterface $field): bool
    {


        return true;
    }

    public function transform($value, Field $field, CsvReportOptions $reportOptions, array $config)
    {
        $value = $this->map($value, $field, $reportOptions, $config);

        // transformations for all transformers
        if (!$reportOptions->keepNewLines) {
            $value = str_replace("\n", $reportOptions->newLine, $value);
            $value = str_replace("\r", "", $value);
        }
        return $value;
    }

    /**
     * @inheritDoc
     */
    private function map($value, Field $field, CsvReportOptions $reportOptions, array $config)
    {

        try {

            if ($field instanceof SproutFields\Address) {
                return $this->mapSproutAddress($value, $field, $reportOptions, $config);
            }

            if ($field instanceof SproutFields\Date) {
                return $this->mapSproutDate($value, $field, $reportOptions, $config);
            }

            if ($field instanceof SproutFields\FileUpload) {
                return $this->mapSproutFileUpload($value, $field, $reportOptions, $config);
            }

            if ($field instanceof SproutFields\Name) {
                return $this->mapSproutName($value, $field, $reportOptions, $config);
            }

            // handle special sprout fields
            if ($field instanceof SproutFields\OptIn) {
                return $this->mapSproutOptin($value, $field, $reportOptions, $config);
            }

            if ($field instanceof SproutFields\Phone) {
                return $this->mapSproutPhone($value, $field, $reportOptions, $config);
            }

            if (is_string($value)) {
                return (string)$value;
            }
            if (is_null($value)) {
                return $reportOptions->nullValue;
            }

            if ($value instanceof MultiOptionsFieldData) {
                return $this->mapMultiOptionsFieldData($value, $reportOptions, $config);
            }
            if ($value instanceof SingleOptionFieldData) {
                return $this->mapSingleOptionsFieldData($value, $reportOptions, $config);
            }
            if ($value instanceof EntryQuery) {
                return $this->mapEntryQuery($value, $reportOptions, $config);
            }
            if ($value instanceof CategoryQuery) {
                return $this->mapCategoryQuery($value, $reportOptions, $config);
            }
            if ($value instanceof TagQuery) {
                return $this->mapTagQuery($value, $reportOptions, $config);
            }
            if ($value instanceof UserQuery) {
                return $this->mapUserQuery($value, $reportOptions, $config);
            }
            if ($reportOptions->debug && is_object($value)) {
                return $reportOptions->invalidValue . ' (' . get_class($value) . ')';
            }

            return $reportOptions->invalidValue;
        } catch (\Exception $e) {
            return $reportOptions->invalidValue . ' (' . $e->getMessage() . ')';
        }


    }

    private function mapMultiOptionsFieldData(MultiOptionsFieldData $value, CsvReportOptions $reportOptions, array $config) {
        $values = array_map(static function(OptionData $optionData) {
            return $optionData->value;
        }, $value->getArrayCopy());
        return implode($reportOptions->multiValueSeparator, $values);
    }

    private function mapSingleOptionsFieldData(SingleOptionFieldData $value, CsvReportOptions $reportOptions, array $config) {
        return $value->value;
    }

    private function mapEntryQuery(EntryQuery $value, CsvReportOptions $reportOptions, array $config) {
        $values = array_map(static function(Entry $entry) {
           return $entry->title;
        }, $value->all());
        return implode($reportOptions->multiValueSeparator, $values);
    }

    private function mapTagQuery(TagQuery $value, CsvReportOptions $reportOptions, array $config) {
        $values = array_map(static function(Tag $tag) {
            return $tag->title;
        }, $value->all());
        return implode($reportOptions->multiValueSeparator, $values);
    }

    private function mapUserQuery(UserQuery $value, CsvReportOptions $reportOptions, array $config) {
        $values = array_map(static function(User $user) {
            return $user->username;
        }, $value->all());
        return implode($reportOptions->multiValueSeparator, $values);
    }

    private function mapCategoryQuery(CategoryQuery $value, CsvReportOptions $reportOptions, array $config) {
        $values = array_map(static function(Category $category) {
            return $category->title;
        }, $value->all());
        return implode($reportOptions->multiValueSeparator, $values);
    }


    /**
     * @param SproutModels\Address $value
     * @param SproutFields\Address $field
     * @param CsvReportOptions $options
     * @param array $config
     * @return string
     */
    private function mapSproutAddress($value, SproutFields\Address $field, CsvReportOptions $options, array $config) {

        if (!$value instanceof SproutModels\Address) {
            return $options->invalidValue;
        }
        $text = (string)$value;
        $text = html_entity_decode($text);
        $text = str_replace('<br/>', PHP_EOL, $text);
        $text = strip_tags($text);
        return trim($text);
    }

    /**
     * @param SproutFields\Date $value
     * @param SproutFields\Date | null $field
     * @param CsvReportOptions $options
     * @param array $config
     * @return mixed
     */
    private function mapSproutDate($value, SproutFields\Date $field, CsvReportOptions $options, array $config) {
        if (!$value instanceof \DateTime) {
            return null;
        }
        if ($field->showDate && $field->showTime) {
            return $value->format($options->dateTimeFormat);
        }
        if ($field->showDate) {
            return $value->format($options->dateFormat);
        }
        if ($field->showTime) {
            return $value->format($options->timeFormat);
        }
        return $options->invalidValue;
    }

    /**
     * @param AssetQuery $value
     * @param SproutFields\FileUpload $field
     * @param CsvReportOptions $options
     * @param array $config
     * @return string
     */
    private function mapSproutFileUpload($value, SproutFields\FileUpload $field, CsvReportOptions $options, array $config) {

        if (!$value instanceof AssetQuery) {
            return $options->invalidValue;
        }

        $values = array_map(static function(Asset $asset) {
            return $asset->url();
        }, $value->all());
        return implode($options->multiValueSeparator, $values);
    }

    /**
     * @param Name $value
     * @param SproutFields\Name | null $field
     * @param CsvReportOptions $options
     * @param array $config
     * @return mixed
     */
    private function mapSproutName($value, SproutFields\Name $field, CsvReportOptions $options, array $config) {

        return '' !== (string)$value ? $value->fullName : '';
    }

    /**
     * @param $value
     * @param SproutFields\OptIn $field
     * @param CsvReportOptions $options
     * @param array $config
     * @return string
     */
    private function mapSproutOptin($value, SproutFields\OptIn $field, CsvReportOptions $options, array $config) {
        return $value ? $field->optInValueWhenFalse : $field->optInValueWhenTrue;
    }

    /**
     * @param SproutModels\Phone $value
     * @param SproutFields\Phone | null $field
     * @param CsvReportOptions $options
     * @param array $config
     * @return mixed
     */
    private function mapSproutPhone($value, SproutFields\Phone $field, CsvReportOptions $options, array $config) {
        return '' !== (string)$value ? $value->getRFC3966() : '';
    }

}