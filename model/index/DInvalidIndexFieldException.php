<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\index;

use app\decibel\model\field\DField;

/**
 * Handles an exception occurring when a {@link DFulltextIndex} object
 * has a non-textual field added to it.
 *
 * @author        Timothy de Paris
 */
class DInvalidIndexFieldException extends DIndexException
{
    /**
     * Creates a new {@link DInvalidIndexFieldException}.
     *
     * @param    DIndex $index
     * @param    DField $field
     * @param    string $reason
     *
     * @return    static
     */
    public function __construct(DIndex $index, DField $field, $reason = '')
    {
        parent::__construct(array(
                                'fieldName' => $field->getName(),
                                'indexType' => get_class($index),
                                'indexName' => $index->getName(),
                                'reason'    => $reason,
                            ));
    }
}
