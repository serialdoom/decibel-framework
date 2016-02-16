<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc;

use app\decibel\model\field\DField;
use app\decibel\rpc\debug\DInvalidRemotePathPatternException;
use ArrayAccess;

/**
 * Explains how a URL maps to a {@link DRemoteProcedure} object.
 *
 * @author        Timothy de Paris
 */
class DRemotePath
{
    /**
     * The regular expression for matching URLs.
     *
     * @var        string
     */
    protected $regex;
    /**
     * The {@link DRemoteProcedure} that will render matching URLs.
     *
     * @var        DRemoteProcedure
     */
    protected $remoteProcedure;
    /**
     * Ordered names of fields present in the pattern.
     *
     * @var        array
     */
    protected $fieldNames = array();

    /**
     * Creates a new path description.
     *
     * @note
     * Tests will only be undertaken to ensure the pattern is valid when
     * the application is running in debug mode.
     *
     * @param    string           $pattern                The pattern for matching URLs.
     * @param    DRemoteProcedure $remoteProcedure        The {@link DRemoteProcedure}
     *                                                    that will execute matched URLs.
     *
     * @return    static
     * @throw    DInvalidRemotePathPatternException    If the provided pattern is not valid.
     */
    public function __construct($pattern, DRemoteProcedure $remoteProcedure)
    {
        // Test the pattern.
        $this->validatePattern($pattern, $remoteProcedure);
        // Convert the pattern to a regular expression.
        $this->regex = $this->convertPatternToRegex($pattern, $remoteProcedure);
        $this->remoteProcedure = $remoteProcedure;
    }

    /**
     * Converts the provided pattern to a regular expression
     * for the provided remote procedure.
     *
     * @param    string           $pattern                The pattern for matching URLs.
     * @param    DRemoteProcedure $remoteProcedure        The {@link DRemoteProcedure}
     *                                                    that will execute matched URLs.
     *
     * @return    string
     * @throws    DInvalidRemotePathPatternException    If the path cannot be converted
     *                                                to a regular expression.
     */
    protected function convertPatternToRegex($pattern, DRemoteProcedure $remoteProcedure)
    {
        foreach ($remoteProcedure->getFields() as $fieldName => $field) {
            /* @var $field DField */
            // Check if the pattern contains this field.
            $placeholder = "{{$fieldName}}";
            if (strpos($pattern, $placeholder) !== false) {
                // Check if this field can be used in a URL.
                $fieldRegex = $field->getRegex();
                if ($fieldRegex === null) {
                    throw new DInvalidRemotePathPatternException(
                        $pattern,
                        $remoteProcedure,
                        "Field <code>{$fieldName}</code> is not able to be used within a path pattern."
                    );
                }
                // Convert the placeholder in the pattern to a regular expression.
                $pattern = str_replace($placeholder, "({$fieldRegex})", $pattern);
                // Store the name of the field for later use.
                $this->fieldNames[] = $fieldName;
            }
        }

        // Encode a return the regex.
        return '/^' . addcslashes($pattern, '/') . '$/';
    }

    /**
     * Creates a new path description.
     *
     * @param    string           $pattern                The pattern for matching URLs.
     * @param    DRemoteProcedure $remoteProcedure        The {@link DRemoteProcedure}
     *                                                    that will render matched URLs.
     *
     * @return    static
     */
    public static function create($pattern, DRemoteProcedure $remoteProcedure)
    {
        return new static($pattern, $remoteProcedure);
    }

    /**
     * Defines fields available for this object.
     *
     * @return    void
     */
    protected function define()
    {
    }

    /**
     * Attempts to match the provided path.
     *
     * If the pattern matches the path, the {@link DRemoteProcedure} object
     * defined by the path will be returned. The {@link DRemoteProcedure} object
     * will be pre-populated with any variable extracted from the pattern.
     *
     * @param    string      $relativeUrl The URL to match.
     * @param    ArrayAccess $parameters  Parameters from the request.
     *
     * @return    DRemoteProcedure
     */
    public function match($relativeUrl, ArrayAccess $parameters)
    {
        $fieldValues = array();
        if (preg_match($this->regex, $relativeUrl, $fieldValues)) {
            // Remove the first element from the array (the full matching string).
            array_shift($fieldValues);
            // Assign the remaining values to the remote procedure.
            foreach ($this->fieldNames as $position => $fieldName) {
                $this->remoteProcedure->setFieldValue($fieldName, $fieldValues[ $position ]);
            }
            // Apply the request parameters to the remote procedure template.
            $this->remoteProcedure->applyParameters($parameters);

            return $this->remoteProcedure;
        }
    }

    /**
     * Validates a path pattern.
     *
     * @param    string           $pattern                The pattern for matching URLs.
     * @param    DRemoteProcedure $remoteProcedure        The {@link DRemoteProcedure}
     *                                                    that will execute matched URLs.
     *
     * @throw    DInvalidRemotePathPatternException    If the provided pattern is not valid.
     * @return    void
     */
    protected function validatePattern($pattern, DRemoteProcedure $remoteProcedure)
    {
        if (preg_match('/^\/.+/', $pattern)) {
            throw new DInvalidRemotePathPatternException(
                $pattern,
                $remoteProcedure,
                'Pattern cannot begin with a forward slash (/)'
            );
        }
        if (preg_match('/.+[^\/]$/', $pattern)) {
            throw new DInvalidRemotePathPatternException(
                $pattern,
                $remoteProcedure,
                'Pattern must end with a forward slash (/)'
            );
        }
    }
}
