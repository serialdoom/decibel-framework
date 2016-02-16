<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\utility;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\database\debug\DDatabaseLockException;
use app\decibel\database\debug\DDatabaseUnlockException;

/**
 * Provides functionality to lock and unlock tables in a {@link DDatabase}.
 *
 * @author    Timothy de Paris
 */
abstract class DDatabaseLockUtility implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Attempts to put a write lock on all provided tables.
     *
     * @param    array $tableNames List of tables in the database.
     *
     * @throws    DDatabaseLockException        If a lock is not able to be obtained.
     * @return    bool    Returns <code>true</code> if successful.
     *                    If not successful, an exception will be thrown.
     */
    abstract public function lockTables(array $tableNames);

    /**
     * Attempts to unlock any currently locked tables.
     *
     * @throws    DDatabaseUnlockException    If an error occurs when attempting to unlock tables.
     * @return    bool    Returns <code>true</code> if successful.
     *                    If not successful, an exception will be thrown.
     */
    abstract public function unlockTables();
}
