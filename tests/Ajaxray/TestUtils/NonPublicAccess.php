<?php
/**
 * Created by PhpStorm.
 * User: Anis Ahmad <anis.programmer@gmail.com>
 * Date: 3/9/17
 * Time: 11:32 AM
 */

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
    public function invokeMethod(&$object, $methodName, array $parameters = array())
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
    public function invokeProperty(&$object, $propName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $prop = $reflection->getProperty($propName);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }
}