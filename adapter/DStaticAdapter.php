<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\adapter;

/**
 * Static adapter class.
 *
 * @author    Timothy de Paris
 */
trait DStaticAdapter
{
    /**
     * The adapted object instance.
     *
     * @var        DAdaptable
     */
    protected $adaptee;

    /**
     * Initialises an adapter.
     *
     * @param    DAdaptable $adapteee The object instance to be adapted.
     */
    protected function __construct(DAdaptable $adapteee)
    {
        $this->adaptee = $adapteee;
    }

    /**
     * Returns an adapter for the provided object instance.
     *
     * @note
     * This method should be called statically against the base
     * adapter class of the desired type.
     *
     * @param    DAdaptable $adaptee The object instance to be adapted.
     *
     * @return    static
     * @throws    DInvalidAdapterException    If this adapter cannot be used
     *                                        to adapt the provided object instance.
     */
    public static function adapt(DAdaptable $adaptee)
    {
        $adapterType = get_called_class();
        $adapteeType = $adapterType::getAdaptableClass();
        if (!$adaptee instanceof $adapteeType) {
            throw new DInvalidAdapterException(get_called_class(), $adaptee);
        }
        $adapter = $adaptee->getAdapter($adapterType);
        if ($adapter === null) {
            $adapter = new $adapterType($adaptee);
            $adaptee->setAdapter($adapter);
        }

        return $adapter;
    }

    /**
     * Returns the object instance that is being adapted by this adapter.
     *
     * @return    DAdaptable
     */
    public function getAdaptee()
    {
        return $this->adaptee;
    }
}
