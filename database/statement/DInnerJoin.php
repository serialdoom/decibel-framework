<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\statement;

/**
 * Provides information about an SQL inner join.
 *
 * @author        Timothy de Paris
 */
class DInnerJoin extends DJoin
{
    /**
     * Returns this join as SQL.
     *
     * @return    string
     */
    public function __toString()
    {
        return " INNER" . parent::__toString();
    }
}
