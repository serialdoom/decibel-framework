<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc;

use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DTextField;
use app\decibel\utility\DUtilityData;

/**
 * Utility class for providing information about a remote procedure.
 *
 * @author        Timothy de Paris
 */
class DRemoteProcedureInformation extends DUtilityData
{
    /**
     * 'Qualified Name' field name.
     *
     * @var        string
     */
    const FIELD_QUALIFIED_NAME = 'qualifiedName';
    /**
     * 'URL' field name.
     *
     * @var        string
     */
    const FIELD_URL = 'url';

    /**
     * Creates a new remote procedure information object.
     *
     * @param    string $qualifiedName    Qualified name of the remote procedure.
     * @param    string $url              URL at which the remote procedure
     *                                    can be accessed on this server.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the provided value is not valid
     *                                        for the field.
     */
    public function __construct($qualifiedName, $url)
    {
        parent::__construct();
        $this->setFieldValue(self::FIELD_QUALIFIED_NAME, $qualifiedName);
        $this->setFieldValue(self::FIELD_URL, $url);
    }

    /**
     * Defines fields available for this utility data object
     *
     * @return    void
     */
    protected function define()
    {
        $url = new DTextField(self::FIELD_URL, 'URL');
        $url->setMaxLength(255);
        $this->addField($url);
        // This used to be a DQualifiedNameField, however as this can store
        // the name of an RPC that exists on the foreign server but not
        // the local server, this can cause a DInvalidFieldValueException
        // to be thrown.
        $qualifiedName = new DTextField(self::FIELD_QUALIFIED_NAME, 'Qualified RPC Name');
        $qualifiedName->setMaxLength(100);
        $this->addField($qualifiedName);
    }

    /**
     * Returns the qualified name of the remote procedure.
     *
     * @return    string
     */
    public function getQualifiedName()
    {
        return $this->getFieldValue(self::FIELD_QUALIFIED_NAME);
    }

    /**
     * Returns the URL at which the remote procedure can be accessed.
     *
     * @return    string
     */
    public function getUrl()
    {
        return $this->getFieldValue(self::FIELD_URL);
    }
}
