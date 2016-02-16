<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache\debug;

/**
 * Handles an exception occurring when invalid serialised data
 * is retrieved from a cache.
 *
 * @author        Timothy de Paris
 */
class DSerializationException extends DCacheException
{
    /**
     * Creates a new {@link DSerializationException}.
     *
     * @param    string $data  The invalid serialised data.
     * @param    string $cache Qualified name of the cache.
     *
     * @return    static
     */
    public function __construct($data, $cache)
    {
        parent::__construct(array(
                                'data'  => $data,
                                'cache' => $cache,
                            ));
    }
}
