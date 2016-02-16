<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\authorise\DUser;

/**
 * A class that can have instance data persisted to the application database.
 *
 * @author        Timothy de Paris
 */
interface DPersistable
{
    /**
     * Performs functionality to ensure no unneccessary information is stored
     * in the database table for this persistable object.
     *
     * @return    void
     */
    //	public static function cleanDatabase();
    /**
     * Deletes the class instance from the database.
     *
     * @param    DUser $user The user attempting to delete the model instance.
     *
     * @return    DResult
     */
    public function delete(DUser $user);

    /**
     * Returns the unique ID for this persistable instance.
     *
     * @return    mixed
     */
    public function getId();

    /**
     * Saves data from the class instance from the database.
     *
     * @param    DUser $user The user attempting to save the model instance.
     *
     * @return    DResult
     */
    public function save(DUser $user);
}
