<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\application;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\cache\DCacheHandler;
use app\decibel\file\DLocalFileSystem;
use app\decibel\regional\DLabel;
use app\decibel\utility\DResult;
use app\decibel\utility\DString;

/**
 * Provides functionality to uninstall an App.
 *
 * This class provides basic uninsallation functionality for Apps, however should be
 * extended should an App require any additional integrity checking before installation,
 * or any further tasks to properly uninstall it.
 *
 * @author    Timothy de Paris
 */
class DAppUninstaller implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Specifies whether this App can be uninstalled by the user.
     *
     * @warning
     * It is the App developer's responsibility to ensure that all neccessary functionality
     * required to safely remove the App without adversely affecting any remaining Apps
     * or data is implemented within the {@link DAppUninstaller::customUninstall()} method.
     *
     * @return    DResult
     */
    public function canUninstall()
    {
        return $this->isDependencyFree();
    }

    /**
     * Undertakes custom uninstallataion requirements for an App.
     *
     * @return    DResult
     */
    protected function customUninstall()
    {
        return null;
    }

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DApp::class;
    }

    /**
     * Checks if any other installed App has a dependency on this App.
     *
     * @return    DResult
     */
    protected function isDependencyFree()
    {
        $result = new DResult();
        $dependentApps = $this->adaptee->getDependentApps();
        if ($dependentApps) {
            $appNames = array();
            foreach ($dependentApps as $app) {
                /* @var $app DApp */
                $appNames[] = $app->getName();
            }
            $result->setSuccess(false, 'This App is required by ' . DString::implode($appNames, ', ', ' and ') . '.');
        }

        return $result;
    }

    /**
     * Uninstalls the App, removing all related scripts and data.
     *
     * @return    DResult
     */
    final public function uninstall()
    {
        $result = new DResult(
            new DLabel(DApp::class, 'app'),
            new DLabel(DApp::class, 'uninstalled')
        );
        $result->merge($this->canUninstall());
        if ($result->isSuccessful()) {
            // Perform custom uninstallation functionality for this App.
            $result->merge($this->customUninstall());
            $path = $this->adaptee->getAbsolutePath();
            $fileSystem = new DLocalFileSystem();
            $fileSystem->deltree($path);
            // Clear all caches.
            DCacheHandler::clearCaches();
        }

        return $result;
    }
}
