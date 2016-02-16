<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

/**
 * Handles an exception occurring when an included file
 * does not declare a class.
 *
 * @author    Timothy de Paris
 */
class DEmptyClassFileException extends DClassInformationException
{
    /**
     * Non-translatable message for this exception.
     *
     * @var        string
     */
    const MESSAGE = 'No class was declared when including file <code>%s</code>.';

    /**
     * Creates a new {@link DEmptyClassFileException}.
     *
     * @param    string $filename Name of the included file.
     *
     * @return    static
     */
    public function __construct($filename)
    {
        parent::__construct(array(
                                'filename' => $filename,
                            ));
    }
}
