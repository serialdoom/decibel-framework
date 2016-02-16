<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional;

use app\decibel\regional\DRegionalException;

/**
 * Handles an exception occurring when plural forms are not available
 * for a language.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DMissingPluralFormException extends DRegionalException
{
    /**
     * Creates a new DMissingPluralFormException.
     *
     * @param    mixed  $pluralForm   The plural form.
     * @param    string $variable     The variable name.
     * @param    string $namespace    Namespace of the label.
     * @param    string $name         Name of the label.
     * @param    string $languageCode The language code.
     *
     * @return    static
     */
    public function __construct($pluralForm, $variable, $namespace, $name, $languageCode)
    {
        parent::__construct(array(
                                'pluralForm'   => $pluralForm,
                                'variable'     => $variable,
                                'namespace'    => $namespace,
                                'name'         => $name,
                                'languageCode' => $languageCode,
                            ));
    }
}
