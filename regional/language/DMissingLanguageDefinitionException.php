<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional\language;

use app\decibel\regional\DRegionalException;

/**
 * Handles an exception occurring when a language definition is not available
 * for a language.
 *
 * @section   versioning Version Control
 *
 * @author    Timothy de Paris
 */
class DMissingLanguageDefinitionException extends DRegionalException
{
    /**
     * Creates a new {@link DMissingLanguageDefinitionException}.
     *
     * @param    string $languageCode The language code.
     *
     * @return    static
     */
    public function __construct($languageCode)
    {
        parent::__construct(array(
                                'languageCode' => $languageCode,
                            ));
    }
}
