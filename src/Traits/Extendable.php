<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Traits;

use Closure;
use BadMethodCallException;

/**
 * Make a class dynamically extendable.
 */
trait Extendable
{
    /**
     * @var array
     */
    protected static $extensions;

    public static function hasExtension($methodName)
    {
        return isset(static::$extensions[$methodName]);
    }


    /**
     * Add a method to the class.
     *
     * @param string   $methodName
     * @param callable $callable
     *
     * @return $this
     */
    public static function extend(string $methodName, callable $callable)
    {
        static::$extensions[$methodName] = $callable;
    }

    /**
     * Mix in all the public methods of an object.
     *
     * @param object $object
     *
     * @return $this
     */
    public static function mixin(object $object)
    {
        $refObject = new ReflectionObject($object);

        $methods = $refObject->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $refMethod) {
            $this->extend($refMethod->name, $refMethod->getClosure($object));
        }

        return $this;
    }

    /**
     * Execute a static call with the correct class scope.
     *
     * @param callable $callable
     * @param array $args
     *
     * @return mixed
     */
    public function execDynamicCallable(callable $callable, array $args)
    {
        if ($callable instanceof Closure) {
            return $callable->call($this, ...$args);
        }

        return $callable(...$args);
    }

    /**
     * Execute a static call with the correct class scope.
     *
     * @param callable $callable
     * @param array $args
     *
     * @return mixed
     */
    public static function execStaticCallable(callable $callable, array $args)
    {
        if ($callable instanceof Closure) {
            Closure::bind($callable, null, static::class)->call(...$args);
        }

        return $callable(...$args);
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
            return $this->execDynamicCallable(static::$extensions[$methodName], $args);
        }

        if (is_callable([parent::class, '__call'])) {
            return parent::__call($methodName, $args);
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

        if (is_callable([parent::class, '__callStatic'])) {
            return parent::__callStatic($methodName, $args);
        }

        throw new BadMethodCallException("Static method $methodName does not exist.");
    }
}
