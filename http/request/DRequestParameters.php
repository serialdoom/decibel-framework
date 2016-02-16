<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\request;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\utility\DList;
use app\decibel\utility\DSession;
use JsonSerializable;
use stdClass;

/**
 * Defines a list of request parameters.
 *
 * @author    Timothy de Paris
 */
class DRequestParameters extends DList implements JsonSerializable
{
    /**
     * Creates a new {@link DRequestParameters}.
     *
     * @param    array $values
     *
     * @return    static
     */
    public function __construct(array $values = array())
    {
        // Cast string to integer and doubles where appropriate.
        $this->castNumericValues($values);
        parent::__construct($values, true);
    }

    /**
     * Builds a query string from the provided array of parmaeters.
     *
     * @param    array $params List of parameters to include.
     *
     * @return    string    A URL-encoded query string (without a leading '?' character).
     */
    public static function buildQueryString(array $params)
    {
        return http_build_query($params, null, '&', PHP_QUERY_RFC3986);
    }

    /**
     * Converts string values that represent int or double values into the respective type.
     *
     * @param    string $value The value to be cast.
     *
     * @return    mixed    The cast value.
     */
    protected function castNumericValue($value)
    {
        if ((string)((int)$value) === (string)$value) {
            $castValue = (int)$value;
        } else {
            if ((string)((double)$value) === (string)$value) {
                $castValue = (double)$value;
            } else {
                $castValue =& $value;
            }
        }

        return $castValue;
    }

    /**
     * Processes request parameters.
     *
     * Numeric types will be cast to integers or doubles as appropriate.
     *
     * @param    array $values Pointer to the values to be cast.
     *
     * @return    void
     */
    protected function castNumericValues(array &$values)
    {
        foreach ($values as &$value) {
            if (is_numeric($value)) {
                $value = $this->castNumericValue($value);
            }
        }
    }

    /**
     * Parses a JSON string and creates a {@link DRequestParameters} from the result.
     *
     * @param    string $json The JSON to parse.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the provided JSON is not valid.
     */
    public static function createFromJson($json)
    {
        $values = json_decode($json, true);
        if ($values) {
            return new static($values);
        }
        throw new DInvalidParameterValueException(
            'json',
            array(get_called_class(), __FUNCTION__),
            'Valid JSON representing a list of request parameters.'
        );
    }

    /**
     * Returns a specified request parameter.
     *
     * @param    string $name         Name of the parameter to return.
     * @param    mixed  $default      The default return value, if the parameter
     *                                cannot be found.
     *
     * @return    mixed    The parameter value, or the default value if no
     *                    parameter could be found with the specified name.
     */
    public function get($name, $default = null)
    {
        if (isset($this->values[ $name ])) {
            $value = $this->values[ $name ];
        } else {
            $value = $default;
        }

        return $value;
    }

    /**
     * Returns the parameters encoded as a query string.
     *
     * The '?' symbol will be included if any parameters are available,
     * otherwise an empty string will be returned.
     *
     * @return    string
     */
    public function getQueryString()
    {
        if ($this->values) {
            $queryString = '?' . static::buildQueryString($this->values);
        } else {
            $queryString = '';
        }

        return $queryString;
    }

    /**
     * Returns a stdClass object ready for encoding into JSON format.
     *
     * @return    stdClass
     */
    public function jsonSerialize()
    {
        return (object)$this->values;
    }

    /**
     * Returns a specified parameter from the request, or the session.
     *
     * The parameter will be maintained in the session after being returned.
     * If it is available in the request, it will be updated in the session.
     * If it is not    in the request but has already been stored in the session,
     * it will be returned from there.
     *
     * @param    string $name         Name of the parameter to return.
     * @param    mixed  $default      The default return value if the parameter
     *                                cannot be found.
     *
     * @return    mixed    The parameter value, or the default value if no
     *                    parameter could be found with the specified name.
     */
    public function persist($name, $default = null)
    {
        $session = DSession::load();
        $sessionId = self::class . '-' . $name;
        // If a value is present in the request,
        // use this and persist it in the session.
        if (isset($this->values[ $name ])) {
            $value = $this->values[ $name ];
            $session[ $sessionId ] = $value;
            // Otherwise retrieve the persisted value, if possible.
        } else {
            if (isset($session[ $sessionId ])) {
                $value = $session[ $sessionId ];
                // Or fall back to the default, but don't persist this.
            } else {
                $value = $default;
            }
        }

        return $value;
    }

    /**
     * Clears a peristent request parameter.
     *
     * @param    string $name Name of the parameter to clear.
     *
     * @return    void
     */
    public function unpersist($name)
    {
        // Remove from the session.
        $session = DSession::load();
        $sessionId = self::class . '-' . $name;
        unset($session[ $sessionId ]);
    }
}
