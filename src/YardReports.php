<?php
/**
 * Yard Reports plugin for Craft CMS 3.x
 *
 * Export sprout forms to CSV (Excel supported)
 *
 * @link      https://en.24hoursmedia.com
 * @copyright Copyright (c) 2020 24HOURSMEDIA
 */

namespace twentyfourhoursmedia\yardreports;

use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Elements;
use craft\web\UrlManager;
use twentyfourhoursmedia\yardreports\elements\YardReport;
use twentyfourhoursmedia\yardreports\events\RegisterTransformersEvent;
use twentyfourhoursmedia\yardreports\services\FieldValueTransformerService;
use twentyfourhoursmedia\yardreports\services\SproutFormsIntegrationService;
use twentyfourhoursmedia\yardreports\services\transformers\DefaultTransformer;
use twentyfourhoursmedia\yardreports\services\YardCsvReportGeneratorService;
use twentyfourhoursmedia\yardreports\services\YardReportsService as YardReportsServiceService;
use twentyfourhoursmedia\yardreports\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\console\Application as ConsoleApplication;

use yii\base\Event;

/**
 * Class YardReports
 *
 * @author    24HOURSMEDIA
 * @package   YardReports
 * @since     1.0.0
 *
 * @property  YardReportsServiceService $yardReportsService
 * @property  SproutFormsIntegrationService $sproutIntegration
 * @property  FieldValueTransformerService $fieldValueTransformer
 * @property  YardCsvReportGeneratorService $csvReportGenerator
 */
class YardReports extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var YardReports
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
                'sproutIntegration' => SproutFormsIntegrationService::class,
                'fieldValueTransformer' => FieldValueTransformerService::class,
                'csvReportGenerator' => YardCsvReportGeneratorService::class
        ]);

        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'twentyfourhoursmedia\yardreports\console\controllers';
        }

        // Register CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['yard-reports/reports/new'] = 'yard-reports/reports/new';
                $event->rules['yard-reports/reports/<id:\d+>/edit'] = 'yard-reports/reports/edit';
                $event->rules['yard-reports/reports/<id:\d+>/download'] = 'yard-reports/reports/download';
                $event->rules['yard-reports/reports/<id:\d+>/preview'] = 'yard-reports/reports/preview';


                $event->rules['yard-reports/reports/<yardReportId:\d+>/report-fields'] = 'yard-reports/report-fields';
                $event->rules['yard-reports/reports/<yardReportId:\d+>/report-fields/new'] = 'yard-reports/report-fields/new';
                $event->rules['yard-reports/reports/<yardReportId:\d+>/report-fields/<id:\d+>/edit'] = 'yard-reports/report-fields/edit';
            }
        );

        // Register our elements
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            static function (RegisterComponentTypesEvent $event) {
                $event->types[] = YardReport::class;
            }
        );

        // Register transformers
        Event::on(
            FieldValueTransformerService::class,
            FieldValueTransformerService::EVENT_REGISTER_TRANSFORMERS,
            static function (RegisterTransformersEvent $event) {
                $event->register(new DefaultTransformer());
            }
        );


        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'yard-reports',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'yard-reports/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
