<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional;

/**
 * Handles an exception occurring when a requested label cannot be found.
 *
 * @author        Timothy de Paris
 */
class DUnknownLabelException extends DRegionalException
{
    /**
     * Creates a new {@link DUnknownLabelException}.
     *
     * @param    string $namespace    Namespace of the label.
     * @param    string $name         Name of the label.
     * @param    string $languageCode The language code.
     *
     * @return    static
     */
    public function __construct($namespace, $name, $languageCode)
    {
        parent::__construct(array(
                                // Cast namespace to a string, as it may be passed as 'null'
                                'namespace'    => (string)$namespace,
                                'name'         => $name,
                                'languageCode' => $languageCode,
                            ));
    }
}
