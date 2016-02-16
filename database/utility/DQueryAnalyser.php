<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\utility;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\database\DQuery;

/**
 * Query analyser class.
 *
 * @author    Timothy de Paris
 */
abstract class DQueryAnalyser implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Analyses the provided query and returns information about potential
     * execution issues.
     *
     * @param    DQuery $query The executed query.
     *
     * @return    array    List of potential problems, or <code>null</code>
     *                    if the query could not be analysed.
     */
    abstract public function analyse(DQuery $query);

    /**
     * Attempts to explain the execution of the provided SQL by the database.
     *
     * @param    DQuery $query The query to explain.
     *
     * @return    string    An explanation in the form of an HTML table,
     *                    or <code>null</code> if this is not possible.
     */
    abstract public function explain(DQuery $query);
}
