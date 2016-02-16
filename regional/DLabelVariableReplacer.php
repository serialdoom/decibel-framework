<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional;

use app\decibel\debug\DErrorHandler;
use app\decibel\regional\language\DLanguageInformation;
use app\decibel\regional\language\DMissingLanguageDefinitionException;
use app\decibel\registry\DGlobalRegistry;
use NumberFormatter;

/**
 * Replaces the variables within a label.
 *
 * @author    Timothy de Paris
 */
class DLabelVariableReplacer
{
    /**
     * Regular expression for matching variables within a label.
     *
     * @var        string
     */
    const REGEX_VARIABLES = '/{#([^#|]+)#(?:\|([^}]+))?}/';

    /**
     * Replace variables in label.
     *
     * Also takes into consideration the plural forms of words.
     *
     * @param    string $content      Label content.
     * @param    array  $variables    Variable values.
     * @param    string $languageCode Language code.
     * @param    string $namespace    Namespace of the label, for error reporting.
     * @param    string $name         Name of the label, for error reporting.
     *
     * @return    string
     * @throws    DMissingPluralFormException        If the required plural form
     *                                            is not available for a variable.
     * @throws    DMissingLanguageDefinitionException    If a definition of this
     *                                                language is not available
     *                                                and plural forms are required.
     * @throws    DMissingLabelVariableException    If a variable within the label
     *                                            content is not present in the
     *                                            <code>$variables</code> parameter.
     * @throws    DInvalidLabelVariableException    If a variable value within the
     *                                            <code>$variables</code> parameter
     *                                            is not a valid string.
     */
    public static function execute($content, array $variables, $languageCode, $namespace, $name)
    {
        // Apply numberic format to integer and float variables.
        self::formatNumericVariables($variables, $languageCode);
        $matches = null;
        preg_match_all(self::REGEX_VARIABLES, $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $variable = $match[1];
            if (isset($match[2])) {
                $plurals = explode('|', $match[2]);
            } else {
                $plurals = array();
            }
            // Determine the value of the variable.
            $value = self::getValue($variable, $plurals, $variables, $languageCode, $namespace, $name);
            // Perform the replacement.
            $content = str_replace($match[0], $value, $content);
        }

        return $content;
    }

    /**
     * Applies numeric formatting to integer and float typed variables within
     * the provided list of variables.
     *
     * @note
     * This function requires the PHP <code>intl</code> extension
     * to be installed. No formatting will occur if this is not available.
     *
     * @param    array  $variables    The variables to format.
     * @param    string $languageCode The language to format labels for.
     *
     * @return    bool    <code>true</code> if formatting could take place,
     *                    <code>false</code> otherwise.
     */
    protected static function formatNumericVariables(array &$variables, $languageCode)
    {
        // Check that the intl extension is installed.
        if (!class_exists('NumberFormatter')) {
            return false;
        }
        // Create the number formatter.
        $numberFormat = new NumberFormatter($languageCode, NumberFormatter::DECIMAL);
        // Detect and format numbers accordingly
        foreach ($variables as &$value) {
            if (is_int($value) || is_float($value)) {
                $numberFormat->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 0);
                $numberFormat->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 100);
                $value = $numberFormat->format($value);
            }
        }

        return true;
    }

    /**
     * Selects the appropriate plural option for replacement
     * of a pluralised variable within a label.
     *
     * @param    string $languageCode Language code.
     * @param    int    $count        Plurality count.
     * @param    array  $options      Plural options.
     * @param    string $variable     Name of the variable, for error reporting.
     * @param    string $namespace    Namespace of the label, for error reporting.
     * @param    string $name         Name of the label, for error reporting.
     *
     * @return    string
     * @throws    DMissingPluralFormException        If the required plural form
     *                                            is not available for a variable.
     */
    protected static function getPluralOption($languageCode, $count,
                                              array $options, $variable, $namespace, $name)
    {
        $pluralForm = self::getRuleNumberFor($languageCode, $count);
        if (isset($options[ $pluralForm ])) {
            $value = $options[ $pluralForm ];
        } else {
            DErrorHandler::throwException(
                new DMissingPluralFormException(
                    $pluralForm,
                    $variable,
                    $namespace,
                    $name,
                    $languageCode
                )
            );
            $value = array_pop($options);
        }

        return $value;
    }

    /**
     * Getter for retriving the rule number for the passed language code.
     *
     * @param    string $languageCode
     * @param    int    $count
     *
     * @return    int
     * @throws    DMissingLanguageDefinitionException    If a definition of the
     *                                                language is not available.
     */
    protected static function getRuleNumberFor($languageCode, $count)
    {
        $registry = DGlobalRegistry::load();
        $languageInformation = $registry->getHive(DLanguageInformation::class);
        try {
            // Remove any regionalisation from the language code.
            $languageCodeParts = explode('-', $languageCode);
            $languageDefinition = $languageInformation->getLanguageDefinition($languageCodeParts[0]);
            // If not in debug mode, allow execution to continue
            // using English plural rules if no language definition exists.
        } catch (DMissingLanguageDefinitionException $exception) {
            DErrorHandler::throwException($exception);
            $languageDefinition = $languageInformation->getLanguageDefinition('en');
        }

        return $languageDefinition->getPluralForm($count);
    }

    /**
     * Gets the value for a variable, considering plural form where specified.
     *
     * @param    string $variable     The variable name.
     * @param    array  $plurals      Plural forms of the variable, if applicable.
     * @param    array  $variables    Variable values.
     * @param    string $languageCode Language code.
     * @param    string $namespace    Namespace of the label, for error reporting.
     * @param    string $name         Name of the label, for error reporting.
     *
     * @return    string
     * @throws    DMissingPluralFormException        If the required plural form
     *                                            is not available for a variable.
     * @throws    DMissingLanguageDefinitionException    If a definition of this
     *                                                language is not available
     *                                                and plural forms are required.
     * @throws    DMissingLabelVariableException    If a variable within the label
     *                                            content is not present in the
     *                                            <code>$variables</code> parameter.
     * @throws    DInvalidLabelVariableException    If a variable value within the
     *                                            <code>$variables</code> parameter
     *                                            is not a valid string.
     */
    protected static function getValue($variable, array $plurals, array $variables,
                                       $languageCode, $namespace, $name)
    {
        // Check that variable has been provided...
        if (!array_key_exists($variable, $variables)) {
            throw new DMissingLabelVariableException($variable, $namespace, $name);
            // ...and is valid.
        } else {
            if (!settype($variables[ $variable ], 'string')) {
                throw new DInvalidLabelVariableException($variables[ $variable ], $variable, $namespace, $name);
                // If so, check whether this is a plural or standard variable.
            } else {
                if ($plurals) {
                    $value = self::getPluralOption(
                        $languageCode,
                        $variables[ $variable ],
                        $plurals,
                        $variable,
                        $namespace,
                        $name
                    );
                    // Just replace for a standard variable.
                } else {
                    $value = $variables[ $variable ];
                }
            }
        }

        return $value;
    }
}
