<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\debug;

/**
 * Handles an exception occurring when a malformed URL
 * is requested from the server.
 *
 * @author    Timothy de Paris
 */
class DMalformedUrlException extends DHttpException
{
    /**
     * Creates a new {@link DMalformedUrlException}.
     *
     * @param    string $url
     *
     * @return    static
     */
    public function __construct($url)
    {
        parent::__construct(array(
                                'url' => $url,
                            ));
    }
}
