<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

/**
 * Singleton class.
 *
 * Instances of these classes must be accessed via the {@link DSingleton::load}
 * static method.
 *
 * @author        Timothy de Paris
 */
interface DSingleton
{
    public static function load();
}
