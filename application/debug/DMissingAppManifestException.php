<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\application\debug;

/**
 * Handles an exception occurring when an App's manifest cannot be found.
 *
 * @author        Timothy de Paris
 */
class DMissingAppManifestException extends DAppException
{
    /**
     * Creates a new {@link DMissingAppManifestException}.
     *
     * @param    string $manifest      Manifest filename.
     * @param    string $qualifiedName Qualified name of the App.
     *
     * @return    static
     */
    public function __construct($manifest, $qualifiedName)
    {
        parent::__construct(array(
                                'manifest'      => $manifest,
                                'qualifiedName' => $qualifiedName,
                            ));
    }
}
