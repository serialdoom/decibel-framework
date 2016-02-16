<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\packaging;

/**
 * Describes a version-based dependency of a %Decibel package.
 *
 * @section        why Why Would I Use It?
 *
 * A dependency is used to ensure that all requirements of a %Decibel App
 * (or the framework itself) are available in order for that App to run
 * correctly. Fully defining the dependencies of an App ensures that the
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
 * %Decibel defines a range of dependency types that should cover most
 * requirements, however this class can be extended if a custom dependency
 * type is required.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        packaging
 */
abstract class DVersionDependency extends DDependency
{
    /**
     * Determines if the current state of the pre-requisite meets the provided
     * criteria.
     *
     * @param    string $value The value to compare the php version.
     *
     * @return    bool
     */
    protected function compareTo($value)
    {
        $currentState = $this->getCurrentState();
        // This isn't available at all.
        if ($currentState === false) {
            $compare = false;
            // Version doesn't matter.
        } else {
            if ($value === true) {
                $compare = true;
                // Or test the version number.
            } else {
                $compare = version_compare($this->getCurrentState(), $value, '>=');
            }
        }

        return $compare;
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
                if ($this->recommended) {
                    $message = "{$this} version {$this->required} or higher is required, although {$this->recommended} or higher should be installed.";
                } else {
                    $message = "{$this} version {$this->required} or higher is required.";
                }
            } else {
                $message = "{$this} version {$this->recommended} or higher should be installed.";
            }
        }

        return $message;
    }
}
