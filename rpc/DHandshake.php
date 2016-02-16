<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc;

use app\decibel\application\DClassManager;
use app\decibel\authorise\DUser;
use app\decibel\model\field\DTextField;
use app\decibel\regional\DLabel;
use app\decibel\rpc\debug\DInvalidRemoteProcedureException;
use app\decibel\rpc\DRemoteProcedure;
use app\decibel\utility\DJson;

/**
 * Manages the handshake between two Decibel installations when executing
 * a remote procedure using {@link DRemoteProcedureCall}.
 *
 * See the @ref rpc Developer Guide for further information about
 * Remote Procedures.
 *
 * @section        why Why Would I Use It?
 *
 * This remote procedure is called by the {@link DRemoteProcedureCall} class.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        rpc
 */
final class DHandshake extends DRemoteProcedure
{
    /**
     * Determines if the specified user is authorised to execute
     * this remote procedure call.
     *
     * @param    DUser $user The user to authorise.
     *
     * @return    bool    <code>true</code> if the user is able to execute
     *                    the procedure, <code>false</code> otherwise.
     */
    public function authorise(DUser $user)
    {
        return true;
    }

    /**
     * Defines the parameters available for this remote procedure.
     *
     * @return    void
     */
    protected function define()
    {
        $remoteProcedure = new DTextField('remoteProcedure',
                                          new DLabel('app\\decibel\\rpc\\DHandshake', 'remoteProcedure'));
        $remoteProcedure->setRequired(true);
        $this->addField($remoteProcedure);
    }

    /**
     * Executes the remote procedure and returns the result.
     *
     * @return    string        Result of the procedure.
     * @throws    DInvalidRemoteProcedureException    If the qualified name provided
     *                                                in the <code>remoteProcedure</code>
     *                                                parameter is not valid for this
     *                                                Decibel installation.
     */
    public function execute()
    {
        // Check that this remote procedure exists.
        $qualifiedName = $this->remoteProcedure;
        if (!DClassManager::isValidClassName($qualifiedName, 'app\\decibel\\rpc\\DRemoteProcedure')) {
            throw new DInvalidRemoteProcedureException($qualifiedName);
        }
        $remoteProcedure = $qualifiedName::loadWithoutParameters();

        return DJson::encode($remoteProcedure->getInformation());
    }
}
