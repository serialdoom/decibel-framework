<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel;

use app\decibel\application\DApp;

/**
 * The core decibel App.
 *
 * @author        Timothy de Paris
 */
final class Decibel extends DApp
{
    /**
     * Returns a number between 0 and 9 showing the load priority of this App.
     *
     * @return    int
     */
    public function getLoadPriority()
    {
        return 0;
    }
}
