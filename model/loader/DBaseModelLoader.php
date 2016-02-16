<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\loader;

use app\decibel\decorator\DRuntimeDecorator;
use app\decibel\model\DBaseModel;

/**
 * Provides functionality to load a model.
 *
 * @author    Timothy de Paris
 */
abstract class DBaseModelLoader extends DRuntimeDecorator
{
    /**
     * Loads the model with the specified ID.
     *
     * @return    DBaseModel
     */
    abstract public function load($id);

    /**
     * Removes any cached data for the object.
     *
     * @return    void
     */
    abstract public function uncache();

    /**
     * Unloads the object from memory.
     *
     * @return    void
     */
    abstract public function unload();
}
