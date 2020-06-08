<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 06/06/2020
 */

namespace twentyfourhoursmedia\yardreports\models;


use craft\base\Model;

class CsvReportOptions extends Model implements \JsonSerializable
{

    public $csvStyle = 'excel';

    public $addUtf8Bom = true;

    public $exportHeaders = true;

    public $nullValue = 'NULL';

    public $invalidValue = '#VALUE';

    public $keepNewLines = true;

    public $newLine = '\n';

    public $multiValueSeparator = '|';



    public $showCreatedColumn = true;

    public $createdColumnName = 'created';

    public $dateFormat = 'Y-m-d';

    public $dateTimeFormat = \DateTime::ATOM;

    public $timeFormat = 'H:i:s';





    public $debug = false;

    public function setValuesFromArray(array $attrs): self {
        $attrsToMerge = array_intersect_key($attrs, $this->getAttributes());
        $this->setAttributes($attrsToMerge, false);
        return $this;
    }

    /**
     * Fix types after import
     */
    public function fixTypes() : self {
        static $boolCols = ['exportHeaders', 'addUtf8Bom', 'showCreatedColumn', 'keepNewLines', 'debug'];
        $attrs = $this->getAttributes($boolCols);
        foreach ($boolCols as $col) {
            $this->setAttributes([$col, filter_var($attrs[$col], FILTER_VALIDATE_BOOLEAN)]);
        }

        return $this;
    }

    public function jsonSerialize()
    {
        return $this->getAttributes();
    }


}