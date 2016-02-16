<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\utility;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\stream\DReadableStream;
use app\decibel\stream\DStreamReadException;

/**
 * Provides functionality to restore the associated {@link DDatabase} class.
 *
 * @author    Timothy de Paris
 */
abstract class DDatabaseRestoreUtility implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Restores the database from a backup copy.
     *
     * @param    DReadableStream $stream Stream from which the backup will be read.
     *
     * @throws    DStreamReadException If a read error occurs.
     * @return    bool
     */
    abstract public function restore(DReadableStream $stream);
}
