<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

/**
 * Handles an exception occurring when a file has been incorrectly namespaced.
 *
 * @author        Timothy de Paris
 */
class DIncorrectNamespaceException extends DClassInformationException
{
    /**
     * Non-translatable message for this exception.
     *
     * @var        string
     */
    const MESSAGE = 'Expected namespace <code>%s</code>, found <code>%s</code> when including file <code>%s</code>. Namespace must match file location.';

    /**
     * Creates a new {@link DIncorrectNamespaceException}.
     *
     * @param    string $expectedNamespace The expected namespace.
     * @param    string $actualNamespace   The actual namespace.
     * @param    string $filename          Name of the included file.
     *
     * @return    static
     */
    public function __construct($expectedNamespace, $actualNamespace, $filename)
    {
        parent::__construct(array(
                                'expectedNamespace' => $expectedNamespace,
                                'actualNamespace'   => $actualNamespace,
                                'filename'          => $filename,
                            ));
    }
}
