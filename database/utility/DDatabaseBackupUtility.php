<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\utility;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\database\debug\DDatabaseBackupException;
use app\decibel\database\debug\DDatabaseLockException;
use app\decibel\database\debug\DDatabaseUnlockException;
use app\decibel\stream\DStreamWriteException;
use app\decibel\stream\DWritableStream;

/**
 * Provides functionality to backup the associated {@link DDatabase} class.
 *
 * @author    Timothy de Paris
 */
abstract class DDatabaseBackupUtility implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Writes a backup copy of the database.
     *
     * @param    DWritableStream $stream Stream to which the backup will be written.
     *
     * @throws    DStreamWriteException        If a write error occurs on the provided stream.
     * @throws    DDatabaseLockException        If a lock is not able to be obtained.
     * @throws    DDatabaseUnlockException    If an error occurs when attempting to unlock tables.
     * @throws    DDatabaseBackupException    If the backup is unable to be completed
     *                                        for other reasons.
     * @return    bool    Returns <code>true</code> if successful.
     *                    If not successful, an exception will be thrown.
     */
    abstract public function backup(DWritableStream $stream);
}
