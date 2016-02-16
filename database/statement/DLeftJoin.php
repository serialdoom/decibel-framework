<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\statement;

/**
 * Provides information about an SQL left join.
 *
 * @author        Timothy de Paris
 */
class DLeftJoin extends DJoin
{
    /**
     * Returns this join as SQL.
     *
     * @return    string
     */
    public function __toString()
    {
        return " LEFT" . parent::__toString();
    }
}
