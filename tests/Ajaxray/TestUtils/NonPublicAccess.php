<?php

declare(strict_types=1);

namespace Ajaxray\TestUtils;

trait NonPublicAccess
{
    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(object &$object, string $methodName, array $parameters = array()): mixed
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Access protected/private property of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param $propName
     * @return mixed Object property.
     *
     */
    public function invokeProperty(object &$object, string $propName): mixed
    {
        $reflection = new \ReflectionClass(get_class($object));
        $prop = $reflection->getProperty($propName);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }
}