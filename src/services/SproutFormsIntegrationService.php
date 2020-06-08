<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 06/06/2020
 */

namespace twentyfourhoursmedia\yardreports\services;


use barrelstrength\sproutforms\elements\Form as SproutForm;
use craft\base\Component;
use craft\base\Field;
use twentyfourhoursmedia\yardreports\elements\YardReport;
use twentyfourhoursmedia\yardreports\elements\YardReportField;

class SproutFormsIntegrationService extends Component
{

    public function getFormFieldsOptions(SproutForm $form)
    {
        $fields = $form->getFields();
        $options = [];
        foreach ($fields as $field) {
            $options[$field->id] = $field->name;
        }
        return $options;
    }

    public function addMissingSproutFields(YardReport $yardReport)
    {
        $elementsService = \Craft::$app->elements;

        $sproutForm = $yardReport->getSproutForm();
        if (!$sproutForm) {
            return;
        }
        $yardFields = $yardReport->getYardFields();

        // get sprout field handles by handle
        $sproutFields = $sproutForm->getFields();
        $sproutIds = array_column($sproutFields, 'id');

        // get registered sprout field handles
        $registeredIds = array_column($yardFields, 'fieldId');
        $unregisteredSproutIds = array_diff($sproutIds, $registeredIds);

        foreach ($unregisteredSproutIds as $fieldId) {
            $yardField = new YardReportField();
            $yardField->structureId = $yardReport->structureId;
            $yardField->yardReportId = $yardReport->id;
            $yardField->fieldId = $fieldId;
            $yardField->transformerHandle = 'default';
            $ok = $elementsService->saveElement($yardField, true);
        }


    }

}