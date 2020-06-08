<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 06/06/2020
 */

namespace twentyfourhoursmedia\yardreports\elements\db;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use twentyfourhoursmedia\yardreports\elements\YardReport;


class YardReportFieldQuery extends ElementQuery
{

    public $yardReportId;

    public $structureId;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->withStructure === null) {
            $this->withStructure = true;
        }

        parent::init();
    }

    /**
     * @param $value
     * @return $this
     */
    public function yardReportId($value)
    {
        $this->yardReportId = $value;

        $report = YardReport::find()->id($value)->one();
        $this->structureId = $report->structureId;

        return $this;
    }

    /**
     * @return bool
     */
    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('yardreport_fields');


        $this->query->select([
            'yardreport_fields.yardReportId',
            'yardreport_fields.fieldId',
            'yardreport_fields.columnName',
            'yardreport_fields.transformerHandle',
        ]);

        if ($this->yardReportId) {
            $this->subQuery->andWhere(Db::parseParam('yardreport_fields.yardReportId', $this->yardReportId));
        }
        return parent::beforePrepare();
    }

}