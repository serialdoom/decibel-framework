<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\request;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\http\debug\DMalformedUrlException;

/**
 * Request wrapper for HTTP POST method.
 *
 * @section   versioning Version Control
 *
 * @author    Timothy de Paris
 */
class DPostRequest extends DEntityBodyRequest
{
    /**
     * 'POST' HTTP method.
     *
     * @var        string
     */
    const METHOD = 'POST';

    /**
     * Validated POST parameters from the request.
     *
     * @var        DRequestParameters
     */
    protected $postParameters;

    /**
     * Merged URL and POST parameters from the request.
     *
     * @var        DRequestParameters
     */
    protected $requestParameters;

    /**
     * Validates request variables ready for access by the application.
     *
     * @param    DRequestInformation    Information about the request. If not provided,
     *                                  a {@link DDefaultRequestInformation} object
     *                                  will be created to determine request information.
     *
     * @return    static
     * @throws    DMalformedUrlException    If the request URL is malformed.
     */
    protected function __construct(DRequestInformation $information)
    {
        parent::__construct($information);
        $this->postParameters = $information->getPostParameters();
        $this->requestParameters = new DRequestParameters();
        $this->requestParameters->merge($this->urlParameters);
        $this->requestParameters->merge($this->postParameters);
        // Check for JSON POST-ed variables
        if (count($this->postParameters) === 0) {
            $postBody = $this->getBody();
            if ($postBody) {
                try {
                    $this->postParameters = DRequestParameters::createFromJson($postBody);
                } catch (DInvalidParameterValueException $exception) {
                }
            }
        }
    }

    /**
     * Called once the singleton object is loaded for the first time.
     *
     * @return    void
     * @throws    DForbidden    If an invalid character is detected in the URI
     *                        or a cross-site scripting attack is detected.
     * @todo    These checks should be moved to a different class.
     */
    public function __wakeup()
    {
        parent::__wakeup();
        // Detect cross site scripting attacks.
        foreach ($this->postParameters as $key => $value) {
            $this->checkXSite($key);
        }
    }

    /**
     * Returns the request parameters for this request.
     *
     * @note
     * This will return combined URL and POST parameters, to return only the POST
     * parameters for ths request use the {@link DPostRequest::getPostParameters()} method.
     *
     * @return    DRequestParameters
     */
    public function getParameters()
    {
        return $this->requestParameters;
    }

    /**
     * Returns the POST parameters for this request.
     *
     * @return    DRequestParameters
     */
    public function getPostParameters()
    {
        return $this->postParameters;
    }
}
