<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\mysql\utility;

use app\decibel\database\DDatabase;
use app\decibel\database\DDatabaseInformation;
use app\decibel\database\debug\DDatabaseBackupException;
use app\decibel\database\debug\DDatabaseLockException;
use app\decibel\database\debug\DDatabaseUnlockException;
use app\decibel\database\DQuery;
use app\decibel\database\DStatementPreparer;
use app\decibel\database\mysql\DMySQL;
use app\decibel\database\utility\DDatabaseBackupUtility;
use app\decibel\database\utility\DDatabaseLockUtility;
use app\decibel\stream\DStreamWriteException;
use app\decibel\stream\DWritableStream;

/**
 * Provides functionality to backup the associated {@link DDatabase} class.
 *
 * @author    Timothy de Paris
 */
class DMySQLBackupUtility extends DDatabaseBackupUtility
{
    /**
     * Writes a backup copy of the database.
     *
     * @param    DWritableStream $stream Stream to which the backup will be written.
     *
     * @return    bool    Returns <code>true</code> if successful.
     *                    If not successful, an exception will be thrown.
     * @throws    DStreamWriteException        If a write error occurs on the provided stream.
     * @throws    DDatabaseLockException        If a lock is not able to be obtained.
     * @throws    DDatabaseUnlockException    If an error occurs when attempting to unlock tables.
     * @throws    DDatabaseBackupException    If the backup is unable to be completed
     *                                        for other reasons.
     */
    public function backup(DWritableStream $stream)
    {
        /* @var $database DDatabase */
        $database = $this->adaptee;
        $databaseInformation = DDatabaseInformation::adapt($database);
        $tableNames = $databaseInformation->getTableNames();
        // Put a write lock on the entire database.
        $lockUtility = DDatabaseLockUtility::adapt($database);
        $lockUtility->lockTables($tableNames);
        // Export database contents.
        foreach ($tableNames as $tableName) {
            $this->backupTable($tableName, $stream);
        }
        // Unlock tables.
        $lockUtility->unlockTables();

        return true;
    }

    /**
     * Dumps a backup of the specified table.
     *
     * @param    string          $tableName Name of the table to export.
     * @param    DWritableStream $stream    Stream to write the export to.
     *
     * @return    void
     * @throws    DStreamWriteException        If a write error occurs on the provided stream.
     */
    protected function backupTable($tableName, DWritableStream $stream)
    {
        /* @var $database DDatabase */
        $database = $this->getDecorated();
        // Build create	query.
        $createSql = "SHOW CREATE TABLE {$tableName}";
        $createQuery = new DQuery($createSql, array(), $database);
        $createRow = $createQuery->getNextRow();
        // Write the table definition to the backup file.
        $stream->write("DROP TABLE IF EXISTS {$tableName};\n");
        $stream->write(array_pop($createRow) . ";\n\n");
        // Retrieve and write each of the rows to the backup.
        $dataSql = "SELECT * FROM `{$tableName}`";
        $dataQuery = new DQuery($dataSql, array(), $database);
        while ($values = $dataQuery->getNextRow()) {
            $this->backupRow($tableName, $values, $stream);
        }
    }

    /**
     * Appends a row of data to the backup.
     *
     * @param    DDatabase       $database    The database being backed up.
     * @param    string          $tableName   Name of the table to export.
     * @param    array           $values      Pointer to the values loaded from
     *                                        the database for this row.
     * @param    DWritableStream $stream      Stream to write the export to.
     *
     * @return    void
     * @throws    DStreamWriteException        If a write error occurs on the provided stream.
     */
    protected function backupRow(DDatabase $database, $tableName,
                                 array &$values, DWritableStream $stream)
    {
        // Escape values.
        foreach ($values as &$value) {
            $value = DStatementPreparer::encloseStringValue(
                $database->escape($value)
            );
        }
        // Build row SQL.
        $rowSql = "INSERT INTO `{$tableName}` VALUES(" . implode(', ', $values) . ");\n";
        // Write to the backup file.
        $stream->write($rowSql);
    }

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DMySQL::class;
    }
}
