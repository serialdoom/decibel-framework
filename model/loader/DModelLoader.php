<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\loader;

use app\decibel\model\DModel;

/**
 * Provides functionality to load a {@link DModel}.
 *
 * @author    Timothy de Paris
 */
class DModelLoader extends DBaseModelLoader
{
    /**
     * Returns the qualified name of the class that can be decorated
     * by this decorator.
     *
     */
    public static function getDecoratedClass()
    {
        return DModel::class;
    }

    /**
     * Loads the model with the specified ID.
     *
     * @return    DModel
     */
    public function load($id)
    { }

    /**
     * Removes any cached data for the object.
     *
     * @return    void
     */
    public function uncache()
    { }

    /**
     * Unloads the object from memory.
     *
     * @return    void
     */
    public function unload()
    { }
}
