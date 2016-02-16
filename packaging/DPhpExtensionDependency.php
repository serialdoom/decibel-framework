<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\packaging;

/**
 * Describes a dependency of a %Decibel package on a PHP extension.
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
 * Requiring a PHP extension:
 *
 * @code
 * <dependency type="app\decibel\packaging\DPhpExtensionDependency">
 *    <name>curl</name>
 *    <required>true</required>
 * </dependency>
 * @endcode
 *
 * Recommending a PHP extension:
 *
 * @code
 * <dependency type="app\decibel\packaging\DPhpExtensionDependency">
 *    <name>curl</name>
 *    <recommended>true</recommended>
 * </dependency>
 * @endcode
 *
 * Providing a custom non-compliance message:
 *
 * @code
 * <dependency type="app\decibel\packaging\DPhpExtensionDependency">
 *    <name>curl</name>
 *    <required>true</required>
 *    <message>PHP \<code\>curl\</code\> extension is required by the Decibel update process</message>
 * </dependency>
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        packaging
 */
class DPhpExtensionDependency extends DDependency
{
    /**
     * Name of the extension.
     *
     * @var        string
     */
    protected $name;

    /**
     * Returns a string describing this DDependency.
     *
     * @return    string
     */
    public function __toString()
    {
        return "PHP <code>{$this->name}</code> extension";
    }

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
        return ($this->getCurrentState() === $value);
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
            $currentState = false;
            $extensions = get_loaded_extensions();
            $names = explode(',', $this->name);
            foreach ($names as $name) {
                if (in_array($name, $extensions)) {
                    $currentState = true;
                    break;
                }
            }
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
                $message = "PHP <code>{$this->name}</code> extension is required.";
            } else {
                $message = "PHP <code>{$this->name}</code> extension is recommended.";
            }
        }

        return $message;
    }
}
