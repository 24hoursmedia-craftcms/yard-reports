<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 07/06/2020
 */
declare(strict_types=1);

namespace twentyfourhoursmedia\yardreports\events;

use twentyfourhoursmedia\yardreports\services\transformers\TransformerInterface;
use yii\base\Event;

class RegisterTransformersEvent extends Event
{

    /**
     * @var array = ['handle' => TransformerInterface]
     */
    public $transformers = [];

    /**
     * @param TransformerInterface $transformer
     */
    public function register(TransformerInterface $transformer) : self {
        $this->transformers[$transformer::getHandle()] = $transformer;
        return $this;
    }


}