<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

/**
 * Handles an exception occurring when invalid page size is provided
 * to a {@link DPagination} instance.
 *
 * @author        Timothy de Paris
 */
class DInvalidPageSizeException extends DPaginationException
{
    /**
     * Creates a new {@link DInvalidPageSizeException}.
     *
     * @param    int $size The invalid page size.
     *
     * @return    static
     */
    public function __construct($size)
    {
        parent::__construct(array(
                                'size' => $size,
                            ));
    }
}
