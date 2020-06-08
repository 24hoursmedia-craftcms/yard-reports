<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 07/06/2020
 */

namespace twentyfourhoursmedia\yardreports\services\transformers;


use craft\base\Field;
use craft\base\FieldInterface;
use twentyfourhoursmedia\yardreports\models\CsvReportOptions;

interface TransformerInterface
{

    /**
     * Return a handle for references to this transformer
     * @return string
     */
    public static function getHandle() : string;

    /**
     * Returns the name of the transformer
     * @return string
     */
    public static function getName() : string;

    /**
     * Wether the transforms supports a given field
     * @param FieldInterface $field
     * @return bool
     */
    public function supports(FieldInterface $field): bool;

    /**
     * Transform the value of field $field to a value that can be published in reports
     *
     * @param $value
     * @param Field $field
     * @param CsvReportOptions $reportOptions
     * @param array $config
     * @return mixed
     */
    public function transform($value, Field $field, CsvReportOptions $reportOptions, array $config);

}