<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\application\debug;

/**
 * Handles an exception occurring when an App's manifest is incomplete.
 *
 * @author        Timothy de Paris
 */
class DMissingAppManifestDataException extends DAppException
{
    /**
     * Creates a new {@link DMissingAppManifestDataException}.
     *
     * @param    string $manifest Manifest filename.
     * @param    string $element  Name of the missing manifest element.
     *
     * @return    static
     */
    public function __construct($manifest, $element)
    {
        parent::__construct(array(
                                'manifest' => $manifest,
                                'element'  => $element,
                            ));
    }
}
