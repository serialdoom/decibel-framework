<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

/**
 * This interface should be implemented by
 * {@link app::decibel::utility::DUtilityData DUtilityData} objects that
 * represent field data which can be persisted to the application database.
 *
 * @author        Timothy de Paris
 */
interface DPersistableFieldData
{
    /**
     * Returns the name of the database table in which field data is stored.
     *
     * @return    string
     */
    public static function getTableName();
}
