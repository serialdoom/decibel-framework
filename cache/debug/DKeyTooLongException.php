<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache\debug;

/**
 * Handles an exception occurring when a caching function is provided with
 * a key that exceeds the maximum allowed length.
 *
 * @author        Timothy de Paris
 */
class DKeyTooLongException extends DCacheException
{
    /**
     * Creates a new {@link DKeyTooLongException}.
     *
     * @param    string $key           The invalid key.
     * @param    int    $maximumLength Maximum allowed key length.
     *
     * @return    static
     */
    public function __construct($key, $maximumLength)
    {
        parent::__construct(array(
                                'key'           => $key,
                                'maximumLength' => $maximumLength,
                            ));
    }
}
