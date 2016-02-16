<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\debug;

/**
 * Handles an exception occurring when requesting information about
 * an unknown model instance.
 *
 * @author        Timothy de Paris
 */
class DUnknownModelInstanceException extends DModelException
{
    /**
     * Creates a new {@link DUnknownModelInstanceException}.
     *
     * @param    int    $id            ID of the unknown model instance.
     * @param    string $qualifiedName Qualified name of the unknown model instance.
     *
     * @return  static
     */
    public function __construct($id, $qualifiedName = null)
    {
        parent::__construct(array(
                                'id'            => $id,
                                'qualifiedName' => $qualifiedName,
                            ));
    }
}
