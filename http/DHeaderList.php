<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

use app\decibel\utility\DList;

/**
 * Represents a list of HTTP headers for a request or response.
 *
 * @author    Timothy de Paris
 */
class DHeaderList extends DList
{
    /**
     * Returns the value for the specified request header.
     *
     * @code
     * use app\decibel\http\request\DRequest;
     *
     * $request = DRequest::load();
     * $headers = $request->getHeaders();
     *
     * // Retrieve a specific HTTP header.
     * debug($headers->getHeader('User-Agent'));
     * @endcode
     *
     * @param    string $name Name of the header.
     *
     * @return    string    The header value, or <code>null</code>> if the requested
     *                    header is not set.
     */
    public function getHeader($name)
    {
        if (isset($this->values[ $name ])) {
            $value = $this->values[ $name ];
        } else {
            $value = null;
        }

        return $value;
    }
}
