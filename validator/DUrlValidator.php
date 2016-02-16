<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\validator;

use app\decibel\http\debug\DMalformedUrlException;
use app\decibel\http\DUrl;
use app\decibel\http\DUrlParser;
use app\decibel\model\DBaseModel;
use app\decibel\model\field\DField;
use app\decibel\utility\DString;
use app\decibel\validator\DValidator;

/**
 * Validates a url. Empty strings are also considered valid.
 *
 * @author        Timothy de Paris
 */
class DUrlValidator extends DValidator
{
    /**
     * Regular expression component to validate Bookmark.
     *
     * @var        string
     */
    const REGEX_BOOKMARK = '(#[^#]*)?';
    /**
     * The option value use to set the options.
     *
     * @var        boolean
     */
    protected $allowFolders = true;
    /**
     * The option value use to set allow file.
     *
     * @var    boolean
     */
    protected $allowFile = true;
    /**
     * The option value use to set allow trailing slash.
     *
     * @var    boolean
     */
    protected $allowTrailingSlash = true;
    /**
     * The option value use to set allow query string.
     *
     * @var    boolean
     */
    protected $allowQueryString = false;
    /**
     * The option value use to set allow bookmark.
     *
     * @var    boolean
     */
    protected $allowBookmark = false;
    /**
     * The option value use to allowed protocols.
     *
     * @var    array
     */
    protected $allowedProtocols = array('http', 'https');
    /**
     * The option value use to require protocols.
     *
     * @var    boolean
     */
    protected $requireProtocol = true;
    /**
     * The option value use to require trailing slash.
     *
     * @var    boolean
     */
    protected $requireTrailingSlash = false;

    /**
     * Set allow folder values
     *
     * @param    boolean $allowFolders
     *
     * @return    void
     */
    public function setAllowFolders($allowFolders)
    {
        $this->allowFolders = $allowFolders;
    }

    /**
     * Get allow folder values
     *
     * @return    boolean
     */
    public function getAllowFolders()
    {
        return $this->allowFolders;
    }

    /**
     * Set allow file values
     *
     * @param    boolean $value The options for validation.
     *
     * @return    void
     */
    public function setAllowFile($value)
    {
        $this->allowFile = $value;
    }

    /**
     * Get allow file values
     *
     * @return    boolean
     */
    public function getAllowFile()
    {
        return $this->allowFile;
    }

    /**
     * Set allow trailing slash value
     *
     * @param    boolean $value The options for validation.
     *
     * @return    void
     */
    public function setAllowTrailingSlash($value)
    {
        $this->allowTrailingSlash = $value;
    }

    /**
     * Get allow trailing slash value
     *
     * @return    boolean
     */
    public function getAllowTrailingSlash()
    {
        return $this->allowTrailingSlash;
    }

    /**
     * Set allow trailing query string value
     *
     * @param    boolean $value The options for validation.
     *
     * @return    void
     */
    public function setAllowQueryString($value)
    {
        $this->allowQueryString = $value;
    }

    /**
     * Get allow trailing query string value
     *
     * @return    boolean
     */
    public function getAllowQueryString()
    {
        return $this->allowQueryString;
    }

    /**
     * Set allow bookmark value
     *
     * @param    boolean $value The options for validation.
     *
     * @return    void
     */
    public function setAllowBookmark($value)
    {
        $this->allowBookmark = $value;
    }

    /**
     * Get allow bookmark value
     *
     * @return    boolean
     */
    public function getAllowBookmark()
    {
        return $this->allowBookmark;
    }

    /**
     * Set allow protocols
     *
     * @param    boolean $value The options for validation.
     *
     * @return    void
     */
    public function setAllowedProtocols($value)
    {
        $this->allowedProtocols = $value;
    }

    /**
     * Get allow protocols
     *
     * @return    array
     */
    public function getAllowedProtocols()
    {
        return $this->allowedProtocols;
    }

    /**
     * Set require protocol
     *
     * @param    boolean $value The options for validation.
     *
     * @return    void
     */
    public function setRequireProtocol($value)
    {
        $this->requireProtocol = $value;
    }

    /**
     * Get require protocol
     *
     * @return    boolean
     */
    public function getRequireProtocol()
    {
        return $this->requireProtocol;
    }

    /**
     * Set require trailing slash
     *
     * @param    boolean $value The options for validation.
     *
     * @return    void
     */
    public function setRequireTrailingSlash($value)
    {
        $this->requireTrailingSlash = $value;
    }

    /**
     * Get require trailing slash
     *
     * @return    boolean
     */
    public function getRequireTrailingSlash()
    {
        return $this->requireTrailingSlash;
    }

    /**
     * Validates data according to the rules of this validation type.
     *
     * @param    mixed      $data  The data to validate.
     * @param    DBaseModel $model The modelthis data is from, if available.
     * @param    DField     $field The field this data is from, if available.
     *
     * @return    array    A list of error messages.
     *                    This will be empty if validation was successful.
     */
    public function validate($data, DBaseModel $model = null, DField $field = null)
    {
        $errors = array();
        // Empty strings are also valid.
        if (strlen($data) == 0) {
            return $errors;
        }
        try {
            $url = DUrlParser::parse($data, false);
            $this->validateTrailingSlash($url, $errors);
            $this->validateProtocol($url, $errors);
            $this->validateBookmark($url, $errors);
            $this->validateQueryString($url, $errors);
            $this->validateUri($url, $errors);
        } catch (DMalformedUrlException $e) {
            $errors[] = '#fieldName# must be a valid URL.';
        }

        return $errors;
    }

    /**
     * Tests that the URL meets the bookmark criteria of this validator.
     *
     * @param    DUrl  $url
     * @param    array $errors
     *
     * @return    void
     */
    protected function validateBookmark(DUrl $url, array &$errors)
    {
        if (!$this->allowBookmark
            && $url->getFragment()
        ) {
            $errors[] = '#fieldName# must not contain a bookmark.';
        }
    }

    /**
     * Validates the protocol requirements of the URL only.
     *
     * @param    DUrl  $url
     * @param    array $errors
     *
     * @return    void
     */
    protected function validateProtocol(DUrl $url, array &$errors)
    {
        $protocol = $url->getProtocol();
        if (!$protocol
            && $this->requireProtocol
        ) {
            $errors[] = "#fieldName# must begin include a protocol (for example, <i>http://</i>)";
        }
        if ($protocol
            && $this->allowedProtocols
            && !in_array($protocol, $this->allowedProtocols)
        ) {
            // Build a friendly error message.
            $protocols = DString::implode(
                $this->getAllowedProtocols(),
                '://</i>, <i>',
                '://</i> or <i>'
            );
            $errors[] = "#fieldName# must begin with <i>{$protocols}://</i>";
        }
    }

    /**
     * Tests that the URL meets the query string criteria of this validator.
     *
     * @param    DUrl  $url
     * @param    array $errors
     *
     * @return    void
     */
    protected function validateQueryString(DUrl $url, array &$errors)
    {
        if (!$this->allowQueryString
            && $url->getQueryParameters()
        ) {
            $errors[] = '#fieldName# must not contain a query string.';
        }
    }

    /**
     * Tests that the URL meets the trailing slash criteria of this validator.
     *
     * @param    DUrl  $url
     * @param    array $errors
     *
     * @return    void
     */
    protected function validateTrailingSlash(DUrl $url, array &$errors)
    {
        $uri = $url->getURI();
        $hasTrailingSlash = ($uri{strlen($uri) - 1} === '/');
        if (!$this->allowTrailingSlash
            && $hasTrailingSlash
        ) {
            $errors[] = '#fieldName# must not end with a forward slash.';
        }
        if ($this->requireTrailingSlash
            && !$hasTrailingSlash
        ) {
            $errors[] = '#fieldName# must end with a forward slash.';
        }
    }

    /**
     * Tests that the URL meets the URI criteria of this validator.
     *
     * @param    DUrl  $url
     * @param    array $errors
     *
     * @return    void
     */
    protected function validateUri(DUrl $url, array &$errors)
    {
        $uri = $url->getURI();
        if (!$this->allowFolders
            && preg_match('/^' . DUrl::REGEX_FOLDERS . '$/', $uri)
        ) {
            $errors[] = '#fieldName# must not contain folders.';
        }
        if ($this->allowFile
            && preg_match('/^' . DUrl::REGEX_FILE . '$/', $uri)
        ) {
            $errors[] = '#fieldName# must not contain a file.';
        }
    }
}
