<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\decorator;

use app\decibel\debug\DInvalidMethodException;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DNotImplementedException;

/**
 * Base decorator class.
 *
 * @author    Timothy de Paris
 */
abstract class DDecorator
{
    /**
     * The decorated object instance.
     *
     * @var        DDecoratable
     */
    private $decorated;

    /**
     * Initialises a decorator.
     *
     * @param    DDecoratable $decorated The decorated object instance.
     *
     * @return    DDecorator
     */
    protected function __construct(DDecoratable $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * Allows methods within the decorated class to be called.
     *
     * @note
     * This method supports chained decorators, with the method being called
     * against the originally decorated object instance.
     *
     * @param    string $name      Name of the called method.
     * @param    array  $arguments Arguments passed to the method.
     *
     * @return    mixed
     * @throws    DInvalidMethodException    If the called method does not
     *                                    exist in the decorated class.
     */
    public function __call($name, array $arguments)
    {
        $decorated = $this->getDecorated();
        if (method_exists($decorated, $name)) {
            return call_user_func_array(array($decorated, $name), $arguments);
        }
        throw new DInvalidMethodException(array(get_class(), $name));
    }

    /**
     * Allows retrieval of property values within the decorated class.
     *
     * @note
     * This method supports chained decorators, with the property value being
     * retrieved from the originally decorated object instance.
     *
     * @param    string $name Name of the property.
     *
     * @return    mixed
     * @throws    DInvalidPropertyException    If the requested property does not
     *                                        exist in the decorated class.
     */
    public function __get($name)
    {
        $decorated = $this->getDecorated();
        if (property_exists($decorated, $name)) {
            return $decorated->$name;
        }
        throw new DInvalidPropertyException($name, get_class());
    }

    /**
     * Allows setting of property values within the decorated class.
     *
     * @note
     * This method supports chained decorators, with the property value being
     * set against the originally decorated object instance.
     *
     * @param    string $name  Name of the property.
     * @param    mixed  $value Updated property value.
     *
     * @return    mixed
     * @throws    DInvalidPropertyException    If the property does not
     *                                        exist in the decorated class.
     */
    public function __set($name, $value)
    {
        $decorated = $this->getDecorated();
        if (property_exists($decorated, $name)) {
            return $decorated->$name = $value;
        }
        throw new DInvalidPropertyException($name, get_class());
    }

    /**
     * Decorates the provided class with this decorator.
     *
     * @note
     * This method should be called statically against the base
     * decorator class of the desired type.
     *
     * @param    DDecoratable $decorated The object to decorate.
     *
     * @return    static
     * @throws    DInvalidDecoratorException    If this decorator cannot be used
     *                                        to decorate the provided instance.
     */
    public static function decorate(DDecoratable $decorated)
    {
        $decoratorType = get_called_class();
        $decoratableClass = $decoratorType::getDecoratedClass();
        if (!$decorated instanceof $decoratableClass) {
            throw new DInvalidDecoratorException(get_called_class(), $decorated);
        }
        $decorator = $decorated->getDecorator($decoratorType);
        if ($decorator === null) {
            $decorator = new $decoratorType($decorated);
            $decorated->setDecorator($decorator);
        }

        return $decorator;
    }

    /**
     * Returns the decorated object.
     *
     * @note
     * This returns to originally decorated object,
     * if this is a chained decorator.
     *
     * @return    DDecoratable
     */
    public function getDecorated()
    {
        $decorated = $this->decorated;
        while ($decorated instanceof DDecorator) {
            $decorated = $decorated->getDecorated();
        }

        return $decorated;
    }

    /**
     * Returns the qualified name of the class that can be decorated
     * by this decorator.
     *
     * @note
     * This method must be overriden by implementing classes.
     *
     * @return    string
     * @throws    DNotImplementedException    If not implemented
     *                                        by an extending class.
     */
    public static function getDecoratedClass()
    {
        throw new DNotImplementedException(array(get_called_class(), __FUNCTION__));
    }
}
