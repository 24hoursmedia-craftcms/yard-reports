<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 06/06/2020
 */

namespace twentyfourhoursmedia\yardreports\services;


use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\elements\Entry as SproutFormEntry;
use craft\base\Component;
use twentyfourhoursmedia\yardreports\elements\YardReport;
use twentyfourhoursmedia\yardreports\elements\YardReportField;
use twentyfourhoursmedia\yardreports\models\GeneratedReport;
use twentyfourhoursmedia\yardreports\models\CsvReportOptions;
use twentyfourhoursmedia\yardreports\YardReports;

class YardCsvReportGeneratorService extends Component implements YardReportGeneratorInterface
{

    // Public Methods
    // =========================================================================

    /*
     * @return GeneratedReport
     */
    public function createReport(YardReport $report, CsvReportOptions $options): GeneratedReport
    {
        $yardFields = YardReportField::find()->yardReportId($report->id)->all();
        $generated = new GeneratedReport();
        $generated->mimeType = 'text/csv';


        $headers = $this->getColumnHeaders($yardFields);
        if ($options->showCreatedColumn) {
            array_unshift($headers, $options->createdColumnName);
        }

        $contents = '';

        if ($options->exportHeaders) {
            $contents = $this->createCsvLine($headers, $options, true);
        }

        $items = $this->getItems($report, $options);
        foreach ($items as $item) {
            $transformed = $this->transformItem($item, $report, $yardFields, $options);

            if ($options->showCreatedColumn) {
                array_unshift($transformed, $item->dateCreated->format($options->dateTimeFormat));
            }

            $contents .= $this->createCsvLine($transformed, $options, true);
        }

        if ($options->addUtf8Bom) {
            $contents = "\xEF\xBB\xBF" . $contents;
        }

        $generated->contents = $contents;
        return $generated;
    }

    /**
     * @param $values
     * @param CsvReportOptions $options
     * @param bool $withEol
     * @return string
     */
    private function createCsvLine($values, CsvReportOptions $options, $withEol = true)
    {
        static $styles = [
            'excel' => ['delimiter' => ';', 'enclosure' => '"', 'escape_char' => "\\"],
            'comma' => ['delimiter' => ',', 'enclosure' => '"', 'escape_char' => "\\"],
            'tab' => ['delimiter' => "\t", 'enclosure' => '"', 'escape_char' => "\\"],
        ];
        $style = $styles[$options->csvStyle];

        $fp = fopen('php://temp', 'r+');
        // Write the array to the target file using fputcsv()
        fputcsv($fp, $values, $style['delimiter'], $style['enclosure'], $style['escape_char']);
        // Rewind the file
        rewind($fp);
        // File Read
        $str = fread($fp, 1048576);
        fclose($fp);


        return rtrim($str) . ($withEol ? PHP_EOL : '');
    }

    /**
     * @param YardReport $report
     * @param CsvReportOptions $options
     * @return Entry[]
     */
    private function getItems(YardReport $report, CsvReportOptions $options)
    {
        $sproutForm = $report->getSproutForm();
        if (!$sproutForm) {
            return [];
        }
        return SproutFormEntry::find()->formId($sproutForm->id)->orderBy('dateCreated DESC')->all();
    }

    /**
     * @param SproutFormEntry $item
     * @param YardReport $report
     * @param YardReportField[] $yardFields
     * @param CsvReportOptions $options
     * @return array
     */
    private function transformItem(SproutFormEntry $item, YardReport $report, array $yardFields, CsvReportOptions $options): array
    {
        $result = [];
        foreach ($yardFields as $yardField) {
            $val = $this->getFieldValue($yardField, $item, $options);
            $result[] = $val;
        }
        return $result;
    }

    /**
     * @param YardReportField $yardField
     * @param SproutFormEntry $item
     * @return mixed|null
     */
    private function getFieldValue(YardReportField $yardField, SproutFormEntry $item, CsvReportOptions $reportOptions)
    {
        $field = $yardField->getField();
        if (!$field) {
            return null;
        }
        $value = $item->getFieldValue($field->handle);
        $transformerHandle = $yardField->transformerHandle;
        return YardReports::$plugin->fieldValueTransformer->transform(
            $value,
            $transformerHandle,
            $field,
            $reportOptions,
            []
        );
    }

    /**
     * @param array $yardFields
     * @return array|int[]|null[]|string[]
     */
    private function getColumnHeaders(array $yardFields)
    {
        return array_map(static function (YardReportField $field) {
            return $field->getReportFieldName();
        }, $yardFields);
    }

}