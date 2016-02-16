<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional;

use app\decibel\debug\DDebuggable;
use app\decibel\debug\DErrorHandler;
use app\decibel\regional\DLanguage;
use app\decibel\regional\DUnknownLabelException;
use app\decibel\utility\DBaseClass;
use Exception;
use JsonSerializable;
use stdClass;

/**
 * Represents a label that has value in particular language and option
 * for a particular namespace.
 *
 * The labels in translation files are in the form:
 *
 * @code
 * app\App\Module-labelName=Label Name
 * @endcode
 *
 * @author    Nikolay Dimitrov
 */
class DLabel implements DDebuggable, JsonSerializable
{
    use DBaseClass;

    /**
     * The label namespace.
     *
     * @var        string
     */
    protected $namespace;

    /**
     * The label name.
     *
     * @var        string
     */
    protected $name;

    /**
     * Variables for substitution into the label.
     *
     * @var        array
     */
    protected $vars;

    /**
     * Creates a new label object.
     *
     * @param    string $namespace    The label namespace, a label identifier, or
     *                                a string that is to be converted to a
     *                                DLabel object.
     * @param    string $name         Name of the label. This must be omitted
     *                                if providing a label identifier.
     * @param    array  $vars         Variables for substitution into the label.
     *
     * @return    static
     * @throws    DUnknownLabelException    If the provided label does not exist.
     */
    public function __construct($namespace, $name, array $vars = array())
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->vars = $vars;
    }

    /**
     * Returns a translation of this label in the current language. If this
     * object was constructed from only one string (that isn't a label
     * identifier) this method will return the provided string.
     *
     * @return    string
     */
    public function __toString()
    {
        try {
            $stringValue = self::translate($this->namespace, $this->name, $this->vars);
        } catch (DUnknownLabelException $exception) {
            $stringValue = "{$this->namespace}-{$this->name}";
            // Just in case something seriously goes wrong...
        } catch (Exception $exception) {
            $stringValue = '';
        }

        return $stringValue;
    }

    /**
     * Provides debugging output for this object.
     *
     * @return    string
     */
    public function generateDebug()
    {
        return array(
            'namespace' => $this->namespace,
            'name'      => $this->name,
        );
    }

    /**
     * Returns a translation for a label.
     *
     * @param    string $namespace        The label namespace.
     * @param    string $name             Name of the label.
     * @param    mixed  $vars             List of variables for substitution into
     *                                    the label. If <code>null</code> is provided,
     *                                    no substitution will take place.
     * @param    string $languageCode     The language to translate into. If not
     *                                    provided the current language will be used.
     * @param    string $scope            Qualified name of the translation scope.
     *
     * @return    string
     * @throws    DUnknownLabelException    If no label can be found.
     */
    public static function translate($namespace, $name, array $vars = array(),
                                     $languageCode = null, $scope = DGlobalTranslationScope::class)
    {
        // Determine language to translate into if required.
        if ($languageCode === null) {
            $languageCode = DLanguage::getDefaultLanguageCode();
        }
        // Try to locate the label.
        $repository = DLabelRepository::load($languageCode);
        $label = $repository->getLabel($name, $namespace, $scope);
        // If not found, throw an exception.
        if ($label === null) {
            throw new DUnknownLabelException($namespace, $name, $languageCode);
        }
        // Replace variables in the label.
        if ($vars) {
            try {
                $label = DLabelVariableReplacer::execute($label, $vars, $languageCode, $namespace, $name);
                // In production mode, allow the label to be returned un-processed
                // and log the exception in the error log.
            } catch (DRegionalException $exception) {
                DErrorHandler::throwException($exception);
            }
        }

        return $label;
    }

    /**
     * Returns the name of this label.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the namespace of this label.
     *
     * @return    string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Returns variables assigned for substitution into this label.
     *
     * @return    array
     */
    public function getVariables()
    {
        return $this->vars;
    }

    /**
     * Returns a copy of this object ready for encoding into json format.
     *
     * @return    stdClass
     */
    public function jsonSerialize()
    {
        return (string)$this;
    }
}
