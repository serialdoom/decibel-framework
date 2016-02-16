<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\mysql\utility;

use app\decibel\database\debug\DDatabaseException;
use app\decibel\database\debug\DDatabaseLockException;
use app\decibel\database\debug\DDatabaseUnlockException;
use app\decibel\database\DQuery;
use app\decibel\database\mysql\DMySQL;
use app\decibel\database\utility\DDatabaseLockUtility;

/**
 * Provides functionality to lock and unlock tables in a {@link DDatabase}.
 *
 * @author    Timothy de Paris
 */
class DMySQLLockUtility extends DDatabaseLockUtility
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DMySQL::class;
    }

    /**
     * Attempts to put a write lock on all provided tables.
     *
     * @param    array $tableNames List of tables in the database.
     *
     * @throws    DDatabaseLockException        If a lock is not able to be obtained.
     * @return    void
     */
    public function lockTables(array $tableNames)
    {
        $exception = null;
        try {
            $lockSql = 'LOCK TABLES ' . implode(' WRITE, ', $tableNames) . ' WRITE;';
            $lockQuery = new DQuery($lockSql, array(), $this->adaptee);
            if (!$lockQuery->isSuccessful()) {
                $exception = $lockQuery->getError();
            }
        } catch (DDatabaseException $exception) {
            // Exception will be converted to a DDatabaseLockException below.
        }
        if ($exception !== null) {
            throw new DDatabaseLockException($exception->getMessage());
        }
    }

    /**
     * Attempts to unlock any currently locked tables.
     *
     * @throws    DDatabaseUnlockException    If an error occurs when attempting to unlock tables.
     * @return    void
     */
    public function unlockTables()
    {
        $exception = null;
        try {
            $unlockQuery = new DQuery('UNLOCK TABLES', array(), $this->adaptee);
            if (!$unlockQuery->isSuccessful()) {
                $exception = $unlockQuery->getError();
            }
        } catch (DDatabaseException $exception) {
            // Exception will be converted to a DDatabaseUnlockException below.
        }
        if ($exception !== null) {
            throw new DDatabaseUnlockException($exception->getMessage());
        }
    }
}
