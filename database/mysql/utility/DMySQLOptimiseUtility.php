<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\mysql\utility;

use app\decibel\database\DDatabase;
use app\decibel\database\DDatabaseInformation;
use app\decibel\database\DQuery;
use app\decibel\database\mysql\DMySQL;
use app\decibel\database\utility\DDatabaseOptimiseUtility;

/**
 * Provides functionality to optimise the associated {@link DDatabase} class.
 *
 * @author    Timothy de Paris
 */
class DMySQLOptimiseUtility extends DDatabaseOptimiseUtility
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
     * Performs any functions neccessary to optimise the database.
     *
     * @return    bool
     */
    public function optimise()
    {
        /* @var $database DDatabase */
        $database = $this->adaptee;
        // Determine which tables will be optimised.
        $tablesToOptimise = $this->getTablesToOptimise();
        // Optimse tables.
        if (count($tablesToOptimise) > 0) {
            $optimiseSql = 'OPTIMIZE TABLE `' . implode('`, `', $tablesToOptimise) . '`;';
            $optimiseQuery = new DQuery($optimiseSql, array(), $database);
            $success = $optimiseQuery->isSuccessful();
        } else {
            $success = true;
        }

        return $success;
    }

    /**
     * Returns a list of tables that need optimising.
     *
     * @return    array
     */
    protected function getTablesToOptimise()
    {
        /* @var $database DDatabase */
        $database = $this->adaptee;
        $tablesToOptimise = array();
        $databaseInformation = DDatabaseInformation::adapt($database);
        foreach ($databaseInformation->getTableInfo() as $table) {
            if ($table['Data_free'] > 0) {
                $tablesToOptimise[] = $table['Name'];
            }
        }

        return $tablesToOptimise;
    }
}
