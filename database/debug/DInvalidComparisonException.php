<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\debug;

/**
 * Handles an exception occurring when an attempt is made to compare incompatible
 * {@link DSchemaElementDefinition} objects.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DInvalidComparisonException extends DSchemaException
{
    /**
     * Creates a new {@link DInvalidComparisonException}.
     *
     * @param    string $reason The reason for the comparison being invalid.
     *
     * @return    static
     */
    public function __construct($reason)
    {
        parent::__construct(array(
                                'reason' => $reason,
                            ));
    }
}
