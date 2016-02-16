<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\rpc\auditing;

use app\decibel\auditing\DAuditRecord;
use app\decibel\model\field\DBooleanField;
use app\decibel\model\field\DIntegerField;
use app\decibel\model\field\DTextField;

/**
 * Provides a record of outbound API requests made by the application.
 *
 * @author    Timothy de Paris
 */
class DOutboundRequestRecord extends DAuditRecord
{
    /**
     * Defines fields and indexes required by this audit record.
     *
     * @return    void
     */
    protected function define()
    {
        $url = new DTextField('url', 'URL');
        $this->addField($url);
        $parameters = new DTextField('parameters', 'Parameters');
        $parameters->setNullOption('None');
        $this->addField($parameters);
        $headers = new DTextField('headers', 'Headers');
        $headers->setNullOption('None');
        $this->addField($headers);
        $postBody = new DTextField('postBody', 'Post Body');
        $postBody->setNullOption('None');
        $this->addField($postBody);
        $username = new DTextField('username', 'Username');
        $username->setNullOption('None');
        $this->addField($username);
        $password = new DBooleanField('password', 'Password');
        $this->addField($password);
        $response = new DTextField('response', 'Response');
        $response->setNullOption('None');
        $this->addField($response);
        $statusCode = new DIntegerField('statusCode', 'HTTP Status Code');
        $response->setNullOption('None');
        $statusCode->setSize(2);
        $this->addField($statusCode);
        $error = new DTextField('error', 'Error Message');
        $error->setNullOption('None');
        $this->addField($error);
    }
}
