<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

/**
 * Handles an exception occurring when a class has been incorrectly named
 * or namespaced.
 *
 * @author        Timothy de Paris
 */
class DIncorrectClassNameException extends DClassInformationException
{
    /**
     * Non-translatable message for this exception.
     *
     * @var        string
     */
    const MESSAGE = 'Expected class name <code>%s</code>, found <code>%s</code> when including file <code>%s</code>. Class name must match filename.';

    /**
     * Creates a new {@link DIncorrectClassNameException}.
     *
     * @param    string $expectedClassName The expected class name.
     * @param    string $actualClassName   The actual class name.
     * @param    string $filename          Name of the included file.
     *
     * @return    static
     */
    public function __construct($expectedClassName, $actualClassName, $filename)
    {
        parent::__construct(array(
                                'expectedClassName' => $expectedClassName,
                                'actualClassName'   => $actualClassName,
                                'filename'          => $filename,
                            ));
    }
}
