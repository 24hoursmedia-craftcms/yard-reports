<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 06/06/2020
 */

namespace twentyfourhoursmedia\yardreports\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use twentyfourhoursmedia\yardreports\elements\YardReport;
use twentyfourhoursmedia\yardreports\elements\YardReportField;
use twentyfourhoursmedia\yardreports\YardReports;

class ReportFieldsController extends Controller
{


    protected function save(YardReportField $element, YardReport $report)
    {

        $request = \Craft::$app->getRequest();
        $element->title = $request->getBodyParam('title');
        $element->yardReportId = $report->id;
        $element->structureId = $report->structureId;
        $element->fieldId = $request->getBodyParam('fieldId');
        $element->columnName = $request->getBodyParam('columnName');
        $element->transformerHandle = $request->getBodyParam('transformerHandle');
        $ok = \Craft::$app->elements->saveElement($element);
        return $ok;
    }

    protected function createViewData(YardReportField $element)
    {

        $yardReport = $element->getYardReport();
        $sproutForm = $yardReport->getSproutForm();

        return [
            'element' => $element,
            'yardReport' => $yardReport,
            'sproutForm' => $sproutForm,
            // supporting services to inject in the twig context
            'sproutIntegration' => YardReports::$plugin->sproutIntegration,
            'fieldValueTransformer' => YardReports::$plugin->fieldValueTransformer,
        ];

    }

    /**
     * actions/yarn-reports/report-fields
     */
    public function actionIndex($yardReportId)
    {
        $report = YardReport::find()->id($yardReportId)->one();

        $elements = YardReportField::find()->yardReportId($yardReportId)->all();

        $data = [
            'report' => $report,
            'elements' => $elements
        ];
        return $this->renderTemplate('yard-reports/report_fields/index.twig', $data);
    }

    public function actionEdit($id, $yardReportId)
    {
        $request = \Craft::$app->getRequest();
        $report = YardReport::find()->id($yardReportId)->one();
        /* @var $report YardReport */
        $element = YardReportField::find()->id($id)->one();
        if ($request->isPost) {
            $ok = $this->save($element, $report);
            if ($ok) {
                Craft::$app->getSession()->setNotice(Craft::t('yard-reports', 'Field updated.'));
                return $this->redirect(UrlHelper::url('yard-reports/reports/' . (int)$yardReportId . '/report-fields'));
            }
        }

        Craft::$app->getSession()->setError(Craft::t('yard-reports', 'Error updating field.'));

        $data = $this->createViewData($element);
        $data['isNew'] = false;

        return $this->renderTemplate('yard-reports/report_fields/edit.twig', $data);

    }

    public function actionNew($yardReportId)
    {
        $request = \Craft::$app->getRequest();
        $report = YardReport::find()->id($yardReportId)->one();
        /* @var $report YardReport */
        $element = new YardReportField();
        $element->yardReportId = $yardReportId;
        $element->structureId = $report->structureId;

        if ($request->isPost) {
            $ok = $this->save($element, $report);
            if ($ok) {
                Craft::$app->getSession()->setNotice(Craft::t('yard-reports', 'New field created.'));
                return $this->redirect(UrlHelper::url('yard-reports/reports/' . (int)$yardReportId . '/report-fields'));
            }
        }
        Craft::$app->getSession()->setNotice(Craft::t('yard-reports', 'Error creating new field'));

        $data = $this->createViewData($element);
        $data['isNew'] = true;

        return $this->renderTemplate('yard-reports/report_fields/edit.twig', $data);
    }

}