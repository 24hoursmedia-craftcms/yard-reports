<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 05/06/2020
 */
namespace twentyfourhoursmedia\yardreports\elements\db;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class YardReportQuery extends ElementQuery
{
    public $sproutFormId;

    /**
     * @param $value
     * @return $this
     */
    public function sproutFormId($value)
    {
        $this->sproutFormId = $value;
        return $this;
    }

    /**
     * @return bool
     */
    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('yardreports');


        $this->query->select([
            'yardreports.sproutFormId',
            'yardreports.structureId',
            'yardreports.csvOptionsJson',
        ]);

        if ($this->sproutFormId) {
            $this->subQuery->andWhere(Db::parseParam('yardreports.sproutFormId', $this->sproutFormId));
        }
        return parent::beforePrepare();
    }

}