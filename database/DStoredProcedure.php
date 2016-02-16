<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database;

use app\decibel\application\DAppManager;

/**
 * Manages stored procedures used to query a database.
 *
 * @author    Timothy de Paris
 */
class DStoredProcedure
{
    /**
     * Stored procedure registration type.
     *
     * @var        string
     */
    const REGISTRATION_STORED_PROC = 'storedProcedure';

    /**
     * Retrieves a stored procedure.
     *
     * @param    string $name Name of the stored procedure.
     *
     * @return    string    The procedure sql, or <code>null</code> if no stored
     *                    procedure exists with the specified name.
     */
    public static function get($name)
    {
        return DAppManager::getRegistration(
            'app\\decibel\\database\\DQuery',
            self::REGISTRATION_STORED_PROC,
            $name
        );
    }

    /**
     * Registers a stored procedure.
     *
     * This function should only be called from an App's Registrations File.
     * See the @ref app_registrations Developer Guide for more information
     * about stored procedures.
     *
     * @param    string $name     Name of the stored procedure. It is recommended
     *                            that stored procedures names are based on the
     *                            namespace of the class that utilises them
     *                            to allow easy identification, for example
     *                            <code>[Namespace]-[Procedure Name]</code>, or
     *                            <code>app\\MyApp\\News\\Article-getLatestArticle</code>
     * @param    mixed  $sql      The procedure sql. This can be a single SQL
     *                            statement provided as a string, or an array
     *                            of multiple SQL statements to be executed
     *                            together.
     *
     * @return    void
     */
    public static function register($name, $sql)
    {
        DAppManager::addRegistration(
            'app\\decibel\\database\\DQuery',
            self::REGISTRATION_STORED_PROC,
            (array)$sql,
            $name
        );
    }
}
