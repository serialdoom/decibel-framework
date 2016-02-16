<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional;

use app\decibel\application\DAppManager;
use app\decibel\regional\DLanguage;
use app\decibel\router\DRouter;
use app\decibel\utility\DUtilityData;

/**
 * Represents a language in which content can be managed within Decibel.
 *
 * @author    Timothy de Paris
 */
class DLanguage extends DUtilityData
{
    ///@cond INTERNAL
    /**
     * Language registration type.
     *
     * @var        string
     */
    const REGISTRATION_LANGUAGE = 'language';
    ///@endcond
    /**
     * Left to right text direction of language.
     *
     * @var        string
     */
    const DIRECTION_LTR = 'ltr';
    /**
     * Right to left text display direction of language.
     *
     * @var        string
     */
    const DIRECTION_RTL = 'rtl';
    /**
     * The array for the available language text directions.
     *
     * @var        array
     */
    public static $availableDirections = array(
        DLanguage::DIRECTION_LTR => 'Left to right',
        DLanguage::DIRECTION_RTL => 'Right to left',
    );
    /**
     * Code representing the language, in one of two forms:
     *
     * - <strong>Non-localised</strong> - <em>ISO 639-1</em> two letter
     *        language code, e.g. <code>en</code> or <code>es</code>
     * - <strong>Localised</strong> - <em>ISO 639-1</em> two letter language
     *        code and <em>ISO 3166-1-alpha-2</em> two letter country code,
     *        e.g. <code>en-gb</code> or <code>en-au</code>
     *
     * @var        string
     */
    protected $code;
    /**
     * Name of the language in the default language for the application
     * (usually English).
     *
     * @var        string
     */
    protected $name;
    /**
     * Local name of the language.
     *
     * @var        string
     */
    protected $localName;
    /**
     * Text display direction in the language.
     *
     * @var        string
     */
    protected $direction;

    /**
     * Creates a new {@link DLanguage} object.
     *
     * @code
     * use app\decibel\regional\DLanguage;
     *
     * $language = new DLanguage(
     *    'es-co',
     *    'Spanish (Colombia)',
     *    'Español',
     *    'co'
     * );
     * @endcode
     *
     * @note
     * It is usually not neccessary to instantiate {@link DLanguage} objects.
     * See @ref multilingual_setup_define in the @ref multilingual_setup
     * Developer Guide for further information about registering languages.
     *
     * @param    string $code      W3C standardised language code.
     * @param    string $name      Name of the language used within Decibel.
     * @param    string $localName Local name of the language.
     * @param    string $direction Directionality of the language.
     *
     * @return    static
     */
    public function __construct($code, $name, $localName,
                                $direction = self::DIRECTION_LTR)
    {
        $this->code = $code;
        $this->name = $name;
        $this->localName = $localName;
        $this->direction = $direction;
    }

    /**
     * Returns a string representation of the language.
     *
     * @code
     * use app\decibel\regional\DLanguage;
     *
     * foreach (DLanguage::getLanguageInformation() as $language) {
     *    debug((string) $language);
     * }
     * @endcode
     *
     * @note
     * This method should not be called directly. It is a magic function that
     * will be called by PHP when casting a {@link DLanguage} object to a string.
     * See http://www.php.net/manual/language.oop5.magic.php for further
     * information.
     *
     * @return    string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Converts a list of language codes into {@link DLanguage} objects.
     *
     * @param    array $languageCodes List of language codes.
     *
     * @return    array    List of {@link DLanguage} objects.
     */
    public static function convertFromCodes(array $languageCodes)
    {
        $objects = array();
        foreach ($languageCodes as $languageCode) {
            $objects[ $languageCode ] = self::getLanguageInformation($languageCode);
        }

        return $objects;
    }

    /**
     * Converts a list of {@link DLanguage} objects into a list of language codes.
     *
     * @param    array $languages List of {@link DLanguage} objects.
     *
     * @return    array    List of string language codes.
     */
    public static function convertToCodes(array $languages)
    {
        $codes = array();
        foreach ($languages as $language) {
            /* @var $language DLanguage */
            $codes[] = $language->getCode();
        }

        return $codes;
    }

    /**
     * Defines fields available for this object.
     *
     * @return    void
     */
    protected function define()
    {
    }

    /**
     * Returns the code representing this language.
     *
     * This can be in one of the following forms:
     * - <strong>Non-localised</strong> - <em>ISO 639-1</em> two letter
     *        language code, e.g. <code>en</code> or <code>es</code>
     * - <strong>Localised</strong> - <em>ISO 639-1</em> two letter language
     *        code and <em>ISO 3166-1-alpha-2</em> two letter country code,
     *        e.g. <code>en-gb</code> or <code>en-au</code>
     *
     * @return    string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns the language code for the current request.
     *
     * @note
     * If called before the {@link DLanguage::initialise()} method,
     * the default language for this installation will be returned.
     *
     * @return    string
     */
    public static function getDefaultLanguageCode()
    {
        if (defined('DECIBEL_REGIONAL_CURRENTLANGUAGE')) {
            $languageCode = DECIBEL_REGIONAL_CURRENTLANGUAGE;
        } else {
            if (defined('DECIBEL_REGIONAL_DEFAULTLANGUAGE')) {
                $languageCode = DECIBEL_REGIONAL_DEFAULTLANGUAGE;
            } else {
                $languageCode = 'en-gb';
            }
        }

        return $languageCode;
    }

    /**
     * Returns the local name for this language.
     *
     * @return    string
     */
    public function getLocalName()
    {
        return $this->localName;
    }

    /**
     * Returns the name of this language.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the text display direction of the language.
     *
     * @return    string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * Registers a language for use within Decibel.
     *
     * @code
     * use app\decibel\regional\DLanguage;
     *
     * DLanguage::registerLanguage(
     *    'es-co',
     *    'Spanish (Colombia)',
     *    'Español',
     *    'co'
     * );
     * @endcode
     *
     * @note
     * This method should only ever be called from within an App Registrations
     * file. If called outside of an App Registrations file the language will
     * only be registered for the current request.
     *
     * See @ref multilingual_setup_define in the @ref multilingual_setup
     * Developer Guide for further information about registering languages.
     *
     * @param    string $code      W3C standardised language code.
     * @param    string $name      Name of the language used within Decibel.
     * @param    string $localName Local name of the language.
     * @param    string $direction Text display direction in the language
     *
     * @return    DLanguage    A pointer to the newly added registration information.
     */
    public static function &registerLanguage($code, $name, $localName,
                                             $direction = self::DIRECTION_LTR)
    {
        return DAppManager::addRegistration(
            get_class(),
            self::REGISTRATION_LANGUAGE,
            new DLanguage($code, $name, $localName, $direction),
            $code
        );
    }

    /**
     * Returns a list of codes for languages registered with Decibel.
     *
     * @code
     * use app\decibel\regional\DLanguage;
     *
     * foreach (DLanguage::getLanguageCodes() as $languageCode) {
     *    debug($languageCode);
     * }
     * @endcode
     *
     * @return    array    A list of language codes.
     */
    public static function getLanguageCodes()
    {
        return array_keys(self::getLanguageInformation());
    }

    /**
     * Returns information about languages registered with Decibel.
     *
     * @code
     * use app\decibel\regional\DLanguage;
     *
     * // Retrieve all languages.
     * foreach (DLanguage::getLanguageInformation() as $language) {
     *    debug((string) $language);
     * }
     *
     * // Retrieve a particular language.
     * debug((string) DLanguage::getLanguageInformation('es-co'));
     * @endcode
     *
     * @param    string $code     If provided, only the DLanguage object
     *                            representing the language with this code will
     *                            be returned.
     *
     * @return    array    A list of {@link DLanguage} objects, or a single
     *                    {@link DLanguage} object if the <code>$code</code>
     *                    parameter was provided.
     */
    public static function getLanguageInformation($code = null)
    {
        $languageRegistration = DAppManager::getRegistration(
            get_class(),
            self::REGISTRATION_LANGUAGE,
            $code
        );
        // This may be called before registrations are cached in which
        // case it will cause errors by returning null.
        if ($languageRegistration !== null) {
            $languageInformation = $languageRegistration;
        } else {
            $languageInformation = array();
        }

        return $languageInformation;
    }

    /**
     * Returns a list of languages registered with Decibel.
     *
     * @code
     * use app\decibel\regional\DLanguage;
     *
     * foreach (DLanguage::getLanguageNames() as $languageCode => $languageName) {
     *    debug($languageName);
     * }
     * @endcode
     *
     * @return    array    A list containing the language codes as keys,
     *                    and names as values.
     */
    public static function getLanguageNames()
    {
        $languages = array();
        foreach (self::getLanguageInformation() as $language) {
            $languages[ $language->getCode() ] = $language->getName();
        }

        return $languages;
    }

    /**
     * Initialises the language in which content will be displayed.
     *
     * @return    void
     */
    public static function initialise()
    {
        // Ask the router for the required language.
        if (DRouter::$router) {
            $languageCode = DRouter::$router->getLanguageCode();
            // Or if no router is active, use the default language
            // for this installation.
        } else {
            $languageCode = DECIBEL_REGIONAL_DEFAULTLANGUAGE;
        }
        // Initialise language constants.
        define('DECIBEL_REGIONAL_CURRENTLANGUAGE', $languageCode);
        DLanguage::setLocalLanguage($languageCode);
    }

    /**
     * Set local information
     *
     * @param    mixed $languageCode Language code, {@link DLanguage} object,
     *                                    or <code>null</code> to return the default
     *                                    language code for the object.
     *
     * @return    void
     */
    protected static function setLocalLanguage($languageCode)
    {
        $codes = explode("-", $languageCode);
        $suffix = strtoupper($codes[0]);
        if (isset($codes[1])) {
            $suffix = strtoupper($codes[1]);
        }
        $code = $codes[0] . "_" . $suffix . ".utf8";
        setlocale(LC_ALL, $code);
    }
}
