<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\packaging;

/**
 * Describes a dependency of a %Decibel package on a particular PHP INI setting.
 *
 * @section        why Why Would I Use It?
 *
 * A dependency is used to ensure that all requirements of a %Decibel App
 * (or the framework itself) are available in order for that App to run
 * correctly. Fully defining the dependencies of an App ensure that the
 * developer installing or upgrading an App does not cause unintended issues
 * simply due to the wrong version of PHP being installed on the system, or not
 * having a recent enough version of the %Decibel framework to support the
 * functionality of the App.
 *
 * @section        how How Do I Use It?
 *
 * Dependencies should be defined within the {@link app_manifests manifest}
 * for an App.
 *
 * @subsection     example Example
 *
 * The following examples should be located within the
 * <code>\<dependencies\></code> section of the manifest for an App.
 *
 * Requiring a particular PHP INI setting:
 *
 * @code
 * <dependency type="app\decibel\packaging\DPhpIniDependency">
 *    <name>display_startup_errors</name>
 *    <required>false</required>
 * </dependency>
 * @endcode
 *
 * Recommending a particular PHP INI setting:
 *
 * @code
 * <dependency type="app\decibel\packaging\DPhpIniDependency">
 *    <name>display_startup_errors</name>
 *    <recommended>false</recommended>
 * </dependency>
 * @endcode
 *
 * Recommending a particular PHP INI setting in production mode only:
 *
 * @code
 * <dependency type="app\decibel\packaging\DPhpIniDependency">
 *    <mode>production</mode>
 *    <name>display_startup_errors</name>
 *    <recommended>false</recommended>
 * </dependency>
 * @endcode
 *
 * Providing a custom non-compliance message:
 *
 * @code
 * <dependency type="app\decibel\packaging\DPhpIniDependency">
 *    <mode>production</mode>
 *    <name>display_startup_errors</name>
 *    <recommended>false</recommended>
 *    <message>PHP \<code\>display_startup_errors\</code\> option should be set to 'Off' when running Decibel in
 *    Production mode</message>
 * </dependency>
 * @endcode
 *
 * @note
 * For boolean INI settings, <code>true</code> and <code>false</code> should be
 * used to represent all available options within the INI file (for example,
 * <code>On</code>, <code>Off</code>, <code>1</code> or <code>0</code>).
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        packaging
 */
class DPhpIniDependency extends DDependency
{
    /**
     * Returns a string describing this DDependency.
     *
     * @return    string
     */
    public function __toString()
    {
        return "PHP INI Setting: {$this->name}";
    }

    /**
     * Name of the INI setting.
     *
     * @var        string
     */
    protected $name;

    /**
     * Determines if the current state of the pre-requisite meets the provided
     * criteria.
     *
     * @param    string $value The current state to compare.
     *
     * @return    bool
     */
    protected function compareTo($value)
    {
        return ($this->getCurrentState() == $value);
    }

    /**
     * Returns the current state of this pre-requisite.
     *
     * @return    mixed
     */
    public function getCurrentState()
    {
        if ($this->currentState) {
            $currentState = $this->currentState;
        } else {
            $currentState = ini_get($this->name);
        }

        return $currentState;
    }

    /**
     * Returns a message describing a pre-requisite failure.
     *
     * @return    string
     */
    protected function getMessage()
    {
        if ($this->message) {
            $message = $this->message;
        } else {
            if ($this->required) {
                $message = $this->getMessageRequired();
            } else {
                $message = $this->getMessageRecommended();
            }
        }

        return $message;
    }

    /**
     * Returns a message describing a recommended pre-requisite failure.
     * @return    string
     */
    protected function getMessageRecommended()
    {
        if ($this->recommended === true) {
            $recommended = 'On';
        } else {
            if ($this->recommended === false) {
                $recommended = 'Off';
            } else {
                $recommended = $this->recommended;
            }
        }

        return "PHP <code>{$this->name}</code> option should be set to '{$recommended}'";
    }

    /**
     * Returns a message describing a required pre-requisite failure.
     * @return    string
     */
    protected function getMessageRequired()
    {
        if ($this->required === true) {
            $required = 'On';
        } else {
            if ($this->required === false) {
                $required = 'Off';
            } else {
                $required = $this->required;
            }
        }

        return "PHP <code>{$this->name}</code> option must be set to '{$required}'";
    }
}
