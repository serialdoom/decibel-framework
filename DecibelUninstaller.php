<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel;

use app\decibel\application\DAppUninstaller;
use app\decibel\utility\DResult;

/**
 * Provides functionality to uninstall {@link Decibel}.
 *
 * @author    Timothy de Paris
 */
class DecibelUninstaller extends DAppUninstaller
{
    /**
     * Specifies whether this App can be uninstalled by the user.
     *
     * @return    DResult
     */
    public function canUninstall()
    {
        return new DResult(null, null, false);
    }

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return Decibel::class;
    }
}
