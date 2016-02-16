<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\health;

use app\decibel\authorise\DUser;
use app\decibel\model\field\DQualifiedNameField;
use app\decibel\rpc\DRemoteProcedure;
use app\decibel\utility\DJson;

/**
 * Executes a specified health check.
 *
 * @author        Timothy de Paris
 */
final class DExecuteHealthCheck extends DRemoteProcedure
{
    /**
     * Determines if the specified user is authorised to execute
     * this remote procedure call.
     *
     * @param    DUser $user The user to authorise.
     *
     * @return    bool        true if the user is able to execute the procedure,
     *                        false otherwise.
     */
    public function authorise(DUser $user)
    {
        return ($user->hasPrivilege('app\\decibel\\maintenance-General'));
    }

    /**
     * Defines the parameters available for this remote procedure.
     *
     * @return    void
     */
    protected function define()
    {
        $healthCheck = new DQualifiedNameField('healthCheck', 'Health Check');
        $healthCheck->setAncestors(array('app\\decibel\\health\\DHealthCheck'));
        $healthCheck->setRequired(true);
        $this->addField($healthCheck);
    }

    /**
     * Executes the remote procedure and returns the result.
     *
     * @return    string        Result of the procedure.
     */
    public function execute()
    {
        $qualifiedName = $this->getFieldValue('healthCheck');
        $healthCheck = new $qualifiedName();

        return DJson::encode($healthCheck->checkHealth());
    }
}
