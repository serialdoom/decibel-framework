<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

/**
 * A model that can be searched using {@link DBaseModelSearch}.
 *
 * @author        Timothy de Paris
 */
interface DSearchable
{
    /**
     * Returns a search object for this model.
     *
     * @return    DBaseModelSearch
     */
    public static function search();
}
