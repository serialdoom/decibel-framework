<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\mysql\utility;

use app\decibel\database\mysql\DMySQL;
use app\decibel\database\utility\DDatabaseRestoreUtility;
use app\decibel\debug\DNotImplementedException;
use app\decibel\stream\DReadableStream;
use app\decibel\stream\DStreamReadException;

/**
 * Provides functionality to restore the associated {@link DDatabase} class.
 *
 * @author    Timothy de Paris
 */
class DMySQLRestoreUtility extends DDatabaseRestoreUtility
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
     * Restores the database from a backup copy.
     *
     * @param    DReadableStream $stream Stream from which the backup will be read.
     *
     * @throws    DStreamReadException If a read error occurs.
     * @return    bool
     */
    public function restore(DReadableStream $stream)
    {
        throw new DNotImplementedException(array(__CLASS__, __FUNCTION__));
    }
}
