<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 07/06/2020
 */
declare(strict_types=1);

namespace twentyfourhoursmedia\yardreports\helpers\traits;
use Craft;
use craft\base\ElementInterface;

trait ElementBufferTrait
{

    private static $_elBuffer = [];

    /**
     * @param string $class
     * @param $id
     * @return ElementInterface|null
     */
    protected function getElementFromStaticBuffer(string $class, $id) {
        if (null === $id || false === $id) {
            return null;
        }
        $key = $class . ':' . $id;
        if (!isset(self::$_elBuffer[$key])) {
            $el = Craft::$app->elements->getElementById((int)$id);
            $el && self::$_elBuffer[$key] = $el;
        }
        return self::$_elBuffer[$key] ?? null;
    }

    protected function getFromStaticBuffer(string $class, $id, \Closure $getter) {
        if (null === $id || false === $id) {
            return null;
        }
        $key = $class . ':' . $id;
        if (!isset(self::$_elBuffer[$key])) {
            $el = $getter($id, $class);
            $el && self::$_elBuffer[$key] = $el;
        }
        return self::$_elBuffer[$key] ?? null;
    }

}