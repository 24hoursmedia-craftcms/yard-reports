<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 07/06/2020
 */

namespace twentyfourhoursmedia\yardreports\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use twentyfourhoursmedia\yardreports\elements\YardReport;
use twentyfourhoursmedia\yardreports\YardReports;

/**
 * Class AddMissingFieldsAction
 *
 * Adds fields from the sprout form to the yard report
 *
 * @package twentyfourhoursmedia\yardreports\elements\actions
 */
class AddSproutFieldsAction extends ElementAction
{

    public $label;


    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('yard-reports', 'Add form fields');
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $integrationService = YardReports::$plugin->sproutIntegration;

        $elements = $query->all();
        foreach ($elements as $element) {
            /* @var $element YardReport */
            $integrationService->addMissingSproutFields($element);
        }
        return true;
    }

}