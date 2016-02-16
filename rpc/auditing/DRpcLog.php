<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\rpc\auditing;

use app\decibel\auditing\DAuditRecord;
use app\decibel\model\field\DQualifiedNameField;
use app\decibel\model\field\DTextField;

/**
 * Defines the base class for RPC logging.
 *
 * @author    Nikolay Dimitrov
 */
class DRpcLog extends DAuditRecord
{
    /**
     * Defines fields and indexes required by this audit record.
     *
     * @return    void
     */
    protected function define()
    {
        $rpc = new DQualifiedNameField('rpc', 'Remote Procedure');
        $rpc->setAncestors(array('app\\decibel\\rpc\\DRemoteProcedure'));
        $this->addField($rpc);
        $mimeType = new DTextField('mimeType', 'MIME Type');
        $mimeType->setMaxLength(128);
        $this->addField($mimeType);
        $requestData = new DTextField('requestData', 'Request Data');
        $this->addField($requestData);
        $requestBody = new DTextField('requestBody', 'Request Body');
        $this->addField($requestBody);
        $responseData = new DTextField('responseData', 'Response Data');
        $this->addField($responseData);
    }
}
