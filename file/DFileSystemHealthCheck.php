<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\file;

use app\decibel\application\DAppManager;
use app\decibel\health\DHealthCheck;
use app\decibel\health\DHealthCheckResult;
use app\decibel\regional\DLabel;
use SplFileInfo;

/**
 * Checks the health of the file system.
 *
 * @author        Timothy de Paris
 */
class DFileSystemHealthCheck extends DHealthCheck
{
    /**
     * Performs the health check.
     *
     * @return    array    List of {@link app::decibel::health::DHealthCheckResult DHealthCheckResult}
     *                    objects.
     */
    public function checkHealth()
    {
        $results = array();
        // Check ownership of Decibel files.
        $currentUser = getmygid();
        $wrongOwner = array();
        $iterator = DRecursiveFileSystemIterator::getIterator(DECIBEL_PATH);
        foreach ($iterator as $file) {
            /* @var $file SplFileInfo */
            $this->checkFileOwnership($file, $currentUser, $wrongOwner);
        }
        if (count($wrongOwner) > 0) {
            $results[] = new DHealthCheckResult(
                DHealthCheckResult::HEALTH_CHECK_ERROR,
                count($wrongOwner) . ' files are not owned by the same user account that Decibel is running under. This can cause failed application updates or stop some features functioning correctly.'
            );
        }

        return $results;
    }

    /**
     * Checks that a file is correctly owned.
     *
     * @param    SplFileInfo $file
     * @param    string      $currentUser
     * @param    array       $wrongOwner
     *
     * @return    void
     */
    protected function checkFileOwnership(SplFileInfo $file, $currentUser, array &$wrongOwner)
    {
        // Ignore links at the moment as these don't seem to work.
        if (!$file->isLink()
            && $file->getOwner() !== $currentUser
        ) {
            $wrongOwner[] = $file->getPathname();
        }
    }

    /**
     * Returns the name of the component being checked.
     *
     * @return    DLabel
     */
    public function getComponentName()
    {
        return new DLabel(DAppManager::class, 'environment');
    }
}
