<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\utility\DResult;

/**
 * Provides functionality for locking and unlocking the {@link DRootUser} account.
 *
 * @author        Timothy de Paris
 */
class DRootUserLock extends DUserLock
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DRootUser::class;
    }

    /**
     * Locks this user's account.
     *
     * @param    string $reason Optional reason for locking the account.
     *
     * @return    DResult
     */
    public function lockAccount($reason = null)
    {
        $result = new DResult('Account', 'locked');
        $result->setSuccess(false, 'Cannot lock the root account.');

        return $result;
    }
}
