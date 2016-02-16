<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise\debug;

use app\decibel\authorise\DUser;

/**
 * Handles an exception occurring when a user does not have
 * the correct privilege to perform an action.
 *
 * @author        Timothy de Paris
 */
class DUnprivilegedException extends DAuthorisationException
{
    /**
     * Creates a new {@link DUnprivilegedException}.
     *
     * @param    DUser  $user      The unprivileged user.
     * @param    string $prigilege Name of the required privilege.
     *
     * @return    static
     */
    public function __construct(DUser $user, $prigilege)
    {
        parent::__construct(array(
                                'user' => (string)$user,
                                'name' => $prigilege,
                            ));
    }
}
