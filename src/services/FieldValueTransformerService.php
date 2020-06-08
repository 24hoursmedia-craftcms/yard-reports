<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 06/06/2020
 */

namespace twentyfourhoursmedia\yardreports\services;


use craft\base\Field;
use twentyfourhoursmedia\yardreports\events\RegisterTransformersEvent;
use twentyfourhoursmedia\yardreports\models\CsvReportOptions;
use yii\base\Component;

class FieldValueTransformerService extends Component
{

    const EVENT_REGISTER_TRANSFORMERS = 'yardreports.register_transformers';


    private $transformers;

    /**
     * @return array = ['handle' => TransformerInterface]
     */
    public function getTransformers() : array {
        if (!$this->transformers) {
            $this->loadTransformersIfNotLoaded();
        }
        return $this->transformers;
    }

    /**
     * Get an array of transformer handles and names for a field id
     *
     * @param $fieldId Field | int
     */
    public function getTransformersOptions($fieldId) {
        $field = null;
        if (!$fieldId instanceof Field && $fieldId) {
            $field = \Craft::$app->fields->getFieldById($fieldId);
        } else {
            $field = $fieldId;
        }
        $transformers = $this->getTransformers();
        $options = [];
        foreach ($transformers as $transformer) {
            if (!$field || $transformer->supports($field)) {
                $options[$transformer::getHandle()] = $transformer::getName();
            }
        }
        return $options;
    }

    public function transform($value, $transformerHandle, Field $field, CsvReportOptions $reportOptions, array $config)
    {
        $this->loadTransformersIfNotLoaded();
        $transformer = $this->transformers[$transformerHandle] ?? null;
        if (!$transformer || !$transformer->supports($field)) {
            return $reportOptions->invalidValue;
        }
        return $transformer->transform($value, $field, $reportOptions, []);
    }

    private function loadTransformersIfNotLoaded() {
        if (!$this->transformers) {
            $event = new RegisterTransformersEvent();
            $this->trigger(self::EVENT_REGISTER_TRANSFORMERS, $event);
            $this->transformers = $event->transformers;
        }
    }


}