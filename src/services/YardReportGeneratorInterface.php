<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 06/06/2020
 */

namespace twentyfourhoursmedia\yardreports\services;


use twentyfourhoursmedia\yardreports\elements\YardReport;
use twentyfourhoursmedia\yardreports\models\GeneratedReport;
use twentyfourhoursmedia\yardreports\models\CsvReportOptions;

interface YardReportGeneratorInterface
{

    public function createReport(YardReport $report, CsvReportOptions $options) : GeneratedReport;

}