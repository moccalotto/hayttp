<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Traits\Common;

use Closure;
use Hayttp\Util;
use ReflectionObject;
use ReflectionMethod;
use BadMethodCallException;

/**
 * Make a class dynamically extendable.
 */
trait Extendable
{
    /**
     * @var array
     */
    protected static $extensions = [];

    /**
     * Has this class been extended with a given method?
     *
     * @param string $methodName The name of the method
     *
     * @return bool
     */
    public static function hasExtension($methodName)
    {
        return isset(static::$extensions[$methodName]);
    }

    /**
     * Add a method to the class.
     *
     * @param string   $methodName
     * @param callable $callable
     */
    public static function extend($methodName, $callable)
    {
        static::$extensions[$methodName] = Util::closureFromCallable($callable);
    }

    /**
     * Mix in all the public methods of an object.
     *
     * @param object $object
     *
     * @return $object
     */
    public static function mixin($object)
    {
        $refObject = new ReflectionObject($object);

        $methods = $refObject->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $refMethod) {
            static::extend($refMethod->name, $refMethod->getClosure($object));
        }

        return $object;
    }

    /**
     * Execute a static call with the correct class scope.
     *
     * @param Closure $closure
     * @param array   $args
     *
     * @return mixed Result of calling $callable(...$args)
     */
    public function execDynamicClosure($closure, array $args)
    {
        $clone = $closure->bindTo($this, $this);

        return call_user_func_array($clone, $args);
    }

    /**
     * Execute a static call with the correct class scope.
     *
     * @param Closure $closure
     * @param array   $args
     *
     * @return mixed Result of calling $callable(...$args)
     */
    public static function execStaticCallable(Closure $closure, array $args)
    {
        $clone = $closure->bindTo(null, static::class);

        return call_user_func_array($clone, $args);
    }

    /**
     * Magic Method for dynamic method names.
     *
     * @param string $methodName
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($methodName, array $args)
    {
        if (isset(static::$extensions[$methodName])) {
            return $this->execDynamicClosure(static::$extensions[$methodName], $args);
        }

        $parent = get_parent_class(static::class);
        if ($parent && method_exists($parent, '__call')) {
            $closure = Util::closureFromCallable([$parent, '__call'])->bindTo($this, $parent);

            return $closure($methodName, $args);
        }

        throw new BadMethodCallException("Method $methodName does not exist.");
    }

    /**
     * Magic Method for dynamic names of static methods.
     *
     * @param string $methodName
     * @param array  $args
     *
     * @return mixed
     */
    public static function __callStatic($methodName, array $args)
    {
        if (isset(static::$extensions[$methodName])) {
            return static::execStaticCallable(static::$extensions[$methodName], $args);
        }

        $parent = get_parent_class(static::class);
        if ($parent && method_exists($parent, '__callStatic')) {
            return $parent::__callStatic($methodName, $args);
        }

        throw new BadMethodCallException("Static method $methodName does not exist.");
    }
}
