<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\rpc;

use app\decibel\authorise\DStatefulSession;
use app\decibel\authorise\DUser;
use app\decibel\authorise\DUserPrivileges;
use app\decibel\debug\DDebuggable;
use app\decibel\http\request\DRequest;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DField;
use app\decibel\rpc\debug\DInvalidRpcParameterException;
use app\decibel\rpc\debug\DMissingRpcParameterException;
use app\decibel\rpc\DRemotelyExecutable;
use app\decibel\rpc\DRemoteProcedure;
use app\decibel\rpc\DRemoteProcedureInformation;
use app\decibel\utility\DDefinable;
use app\decibel\utility\DDefinableObject;
use app\decibel\utility\DString;
use ArrayAccess;

/**
 * Base class for Remote Procedures within %Decibel.
 *
 * Remote Procedures can be implemented to allow the execution of functionality
 * or return of information from the decibel framework via an AJAX or other
 * compatible Remote Procedure Call.
 *
 * By default, Remote Procedures should return a JSON encoded result, however
 * the {@link DRemoteProcedure::getResultType()} method can be overridden to
 * allow XML, HTML, Plain Text or JavaScript code to be returned.
 *
 * More information about executing and writing remote procedures can be found
 * in the @ref rpc Developer Guide.
 *
 * @author         Timothy de Paris
 * @ingroup        rpc
 */
abstract class DRemoteProcedure implements DDefinable, DDebuggable, DRemotelyExecutable
{
    use DDefinableObject;
    /**
     * Holds the request body (if required)
     *
     * @var        string
     */
    private $postBody = null;

    /**
     * Creates the remote procedure.
     *
     * @return    DRemoteProcedure
     */
    protected function __construct()
    {
        $this->fields = array();
        $this->define();
    }

    /**
     * Loads the remote procedure, applying parameters from the current request.
     *
     * @return    DRemoteProcedure
     * @throws    DMissingRpcParameterException    If a required field value is missing.
     * @throws    DInvalidRpcParamtereException    If the value provided for a field
     *                                            is not valid.
     */
    public static function load()
    {
        // Load the rpc and apply current request parameters.
        $rpc = new static();
        $rpc->applyParameters();

        return $rpc;
    }

    /**
     * Loads the remote procedure without validating any parameters.
     *
     * @return    static
     */
    public static function loadWithoutParameters()
    {
        return new static();
    }

    /**
     * Applies parameters from the provided request to the remote procedure.
     *
     * @param    ArrayAccess $parameters  Request parameters to apply. If not provided,
     *                                    the current request will be used.
     *
     * @return    static
     * @throws    DMissingRpcParameterException    If a required field value is missing.
     * @throws    DInvalidRpcParamtereException    If the value provided for a field
     *                                            is not valid.
     */
    public function applyParameters(ArrayAccess $parameters = null, $body = null)
    {
        if ($parameters === null) {
            $request = DRequest::load();
            $parameters = $request->getParameters();
            $body = $request->getBody();
        }
        // Store the post body if required by this remote procedure.
        if ($this->requiresPostBody()) {
            $this->postBody = $body;
        }
        // Validate the parameters on load so that they are
        // available to the DRemoteProcedure::authorise() method.
        $this->validateParameters($parameters);

        return $this;
    }

    /**
     * Determines if the specified user is authorised to execute
     * this remote procedure call.
     *
     * By default, remote procedures are accessible only by root users.
     *
     * @param    DUser $user The user to authorise.
     *
     * @return    bool    <code>true</code> if the user is able to execute
     *                    the procedure, <code>false</code> otherwise.
     */
    public function authorise(DUser $user)
    {
        $userPrivileges = DUserPrivileges::adapt($user);

        return $userPrivileges->hasPrivilege('app\\DecibelCMS-Administration');
    }

    /**
     * Defines the fields available for this remote procedure.
     *
     * @return    void
     */
    protected function define()
    {
    }

    /**
     * Executes the remote procedure and returns the result.
     *
     * Where the {@link DRemoteProcedure::execute} function throws an exception,
     * this exception will be encoded as JSON and returned as the result of the
     * remote procedure. The encoded exception will contain the following data:
     * - <code>_qualifiedName</code>: Qualified name of the exception class.
     * - <code>message</code>: The exception message.
     * - <code>file</code>: The file in which the exception was thrown. Only
     *   present in Debug mode.
     * - <code>line</code>: The line on which the exception was thrown. Only
     *   present in Debug mode.
     *
     * @return    string        Result of the procedure.
     */
    abstract public function execute();

    /**
     * Returns information about this remote procedure.
     *
     * @return    DRemoteProcedureInformation
     */
    public function getInformation()
    {
        $request = DRequest::load();
        $url = $request->getUrl()->getWebsiteRoot() . $this->getRelativeRemoteUrl();

        return new DRemoteProcedureInformation(get_class($this), $url);
    }

    /**
     * Returns the relative URL at which this remote functionality
     * can be accessed.
     *
     * @return    string
     */
    public function getRelativeRemoteUrl()
    {
        return DString::qualifiedNameToPath(
            get_class($this),
            DECIBEL_CORE_RPCPATH
        ) . '/';
    }

    /**
     * Specifies the type of result returned by this Remote Procedure.
     *
     * This will ensure the correct MIME type is returned in the HTTP response
     * allowing the %Decibel JavaScript framework to correctly interpret
     * the result.
     *
     * @return    string    The result type, must be one of
     *                    {@link DRemoteProcedure::RESULT_TYPE_JSON},
     *                    {@link DRemoteProcedure::RESULT_TYPE_XML},
     *                    {@link DRemoteProcedure::RESULT_TYPE_HTML},
     *                    {@link DRemoteProcedure::RESULT_TYPE_TEXT} or
     *                    {@link DRemoteProcedure::RESULT_TYPE_JAVASCRIPT}.
     */
    public function getResultType()
    {
        return DRemoteProcedure::RESULT_TYPE_JSON;
    }

    /**
     * Returns the authenticated user for this request.
     *
     * @return    DUser
     */
    public function getUser()
    {
        $session = $this->getUserSession();
        if ($session !== null) {
            $user = $session->getUser();
        } else {
            $user = DGuestUser::create();
        }

        return $user;
    }

    /**
     * Returns the user session that will be used to authenticate access to the remote procedure.
     *
     * @return    DUserSession    The authenticated session, or <code>null</code> if no authenticated
     *                            session is available.
     */
    public function getUserSession()
    {
        return DStatefulSession::getCurrentSession();
    }

    /**
     * Determines whether the procedure will log execution information
     * to the remote procedure log.
     *
     * By default, remote procedures will not log execution information.
     *
     * @return    bool
     */
    public function log()
    {
        return false;
    }

    /**
     * Determines whether profiling can occur on this remote procedure.
     *
     * By default, profiling is enabled on remote procedures.
     *
     * @return    bool
     */
    public function profile()
    {
        return true;
    }

    /**
     * Determines if this remote procedure is expecting content to be posted
     * in the body of the request.
     *
     * By default, remote procedures do not expect a request body.
     *
     * @return    bool
     */
    protected function requiresPostBody()
    {
        return false;
    }

    /**
     * Reads the post body.
     *
     * @return    bool
     */
    protected function readPostBody()
    {
        return $this->postBody;
    }

    /**
     * Determines if this is required to be executed through HTTPS.
     *
     * If this function returns true, requests made via HTTP will automatically
     * redirect to the HTTPS protocol.
     *
     * Note that it is the responsibility of this function to determine that
     * the server is capable of supporting an HTTPS request, otherwise requests
     * may fail.
     *
     * @return    bool
     */
    public function requiresHttps()
    {
        return false;
    }

    /**
     * Validates the fields provided to the remote procedure.
     *
     * This function should be implemented in all remote procedures that require
     * one or more fields. The function is not abstract for backwards
     * compatibility.
     *
     * @param    ArrayAccess $parameters Parameters passed to the procedure.
     *
     * @return    void
     * @throws    DMissingRpcParameterException    If a required field value is missing.
     * @throws    DInvalidRpcParamtereException    If the value provided for a field
     *                                            is not valid.
     */
    protected function validateParameters(ArrayAccess $parameters)
    {
        foreach ($this->fields as $field) {
            /* @var $field DField */
            $fieldName = $field->getName();
            // Check that required field values are provided.
            $valueAvailable = $field->hasValueInSource($parameters);
            if ($field->isRequired()
                && !isset($this->fieldValues[ $fieldName ])
                && !$valueAvailable
            ) {
                throw new DMissingRpcParameterException($fieldName);
            }
            // Validate provided fields.
            if ($valueAvailable) {
                $this->validateFieldValue($field, $parameters);
            }
        }
    }

    /**
     * Validates the parameter value for a field.
     *
     * @param    DField      $field
     * @param    ArrayAccess $parameters
     *
     * @throws    DInvalidRpcParameterException
     * @return    void
     */
    protected function validateFieldValue(DField $field, ArrayAccess $parameters)
    {
        $fieldName = $field->getName();
        try {
            $this->fieldValues[ $fieldName ] =
                // currently needed to convert linked IDs to objects.
                // @todo Work out how to get rid of this.
            $mapper = $field->getDatabaseMapper();
            $mapper->unserialize(
                $field->castValue(
                    $field->getValueFromSource($parameters)
                )
            );
        } catch (DInvalidFieldValueException $exception) {
            $information = $exception->information;
            throw new DInvalidRpcParameterException(
                $information['value'],
                $information['fieldName'],
                $information['expected']
            );
        }
    }
}
