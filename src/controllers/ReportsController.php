<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 05/06/2020
 */

namespace twentyfourhoursmedia\yardreports\controllers;

use Craft;
use barrelstrength\sproutforms\SproutForms;
use craft\helpers\ElementHelper;
use craft\helpers\UrlHelper;
use \craft\web\Controller;
use twentyfourhoursmedia\yardreports\elements\YardReport;
use twentyfourhoursmedia\yardreports\models\CsvReportOptions;
use twentyfourhoursmedia\yardreports\YardReports;
use yii\web\Response;

class ReportsController extends Controller
{


    protected function save(YardReport $report) {
        $request = \Craft::$app->getRequest();
        $report->title = $request->getBodyParam('title');
        $report->sproutFormId = $request->getBodyParam('sproutFormId');

        $options = $report->getCsvOptions();

        // get all body params prefixed with csvOpts
        $csvOptsArray = [];
        foreach ($request->getBodyParams() as $k => $v) {
            if (0 === strpos($k, 'csvOpts')) {
                $csvOptsArray[lcfirst(substr($k, 7))] = $v;
            }
        }

        $options->setValuesFromArray($csvOptsArray)->fixTypes();

        $report->setCsvOptionsToPersist($options);

        $ok = \Craft::$app->elements->saveElement($report);
        return $ok;
    }

    protected function createViewData(YardReport $report) {
        $data = [];
        $sproutForms = SproutForms::$app->forms->getAllForms();
        $sproutFormOpts = [];
        foreach ($sproutForms as $form) {
            $sproutFormOpts[$form->id] = $form->name;
        }

        return [
            'element' => $report,
            'sproutFormOpts' => $sproutFormOpts
        ];
    }

    /**
     * actions/yarn-reports/reports/{id}/edit
     */
    public function actionEdit($id)
    {
        $request = \Craft::$app->getRequest();
        $report = YardReport::find()->id($id)->one();
        /* @var $report YardReport */

        if ($request->isPost) {
            $ok = $this->save($report);
            if ($ok) {
                if ($request->getBodyParam('addFieldsOnSave') === '1') {
                    YardReports::$plugin->sproutIntegration->addMissingSproutFields($report);
                }
                Craft::$app->getSession()->setNotice(Craft::t('yard-reports', 'Report updated.'));
                return $this->redirect(UrlHelper::url('yard-reports'));
            }
        }
        Craft::$app->getSession()->setError(Craft::t('yard-reports', 'Error updating report.'));

        $data = $this->createViewData($report);
        $data['isNew'] = false;

        return $this->renderTemplate('yard-reports/report/edit.twig', $data);
    }


    /**
     * actions/yarn-reports/report/new
     */
    public function actionNew()
    {
        $request = \Craft::$app->getRequest();
        $report = new YardReport();

        if ($request->isPost) {
            $ok = $this->save($report);
            if ($ok) {
                if ($request->getBodyParam('addFieldsOnSave') === '1') {
                    YardReports::$plugin->sproutIntegration->addMissingSproutFields($report);
                }
                Craft::$app->getSession()->setNotice(Craft::t('yard-reports', 'New report created.'));
                return $this->redirect(UrlHelper::url('yard-reports'));
            }
        }
        Craft::$app->getSession()->setError(Craft::t('yard-reports', 'Error creating new report.'));

        $data = $this->createViewData($report);
        $data['isNew'] = true;

        return $this->renderTemplate('yard-reports/report/edit.twig', $data);
    }

    /**
     * actions/yarn-reports/reports/{id}/download
     */
    public function actionDownload($id)
    {
        $datestamp = date('YmdHis');
        $report = YardReport::find()->id($id)->one();
        /* @var $report YardReport */
        $options = $report->getCsvOptions(new CsvReportOptions());

        $generator = YardReports::$plugin->csvReportGenerator;
        $generatedReport = $generator->createReport($report, $options);
        $response = new Response();
        $response->content = $generatedReport->contents;
        $response->headers->set('content-type', $generatedReport->mimeType);
        $response->headers->set('content-disposition', sprintf(
            'attachment; filename=%s-%s.%s',
            ElementHelper::createSlug($report->title),
            $datestamp,
            'csv'
        ));
        return $response;
    }

    /**
     * actions/yarn-reports/reports/{id}/download
     */
    public function actionPreview($id)
    {
        $report = YardReport::find()->id($id)->one();
        /* @var $report YardReport */
        $options = $report->getCsvOptions(new CsvReportOptions());

        $generator = YardReports::$plugin->csvReportGenerator;
        $generatedReport = $generator->createReport($report, $options);
        $response = new Response();
        $response->content = $generatedReport->contents;
        $response->headers->set('content-type', $generatedReport->mimeType);
        return $response;
    }

}