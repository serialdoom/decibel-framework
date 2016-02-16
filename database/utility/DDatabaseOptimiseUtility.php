<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\utility;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;

/**
 * Provides functionality to optimise the associated {@link DDatabase} class.
 *
 * @author    Timothy de Paris
 */
abstract class DDatabaseOptimiseUtility implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Performs any functions neccessary to optimise the database.
     *
     * @return    bool
     */
    abstract public function optimise();
}
