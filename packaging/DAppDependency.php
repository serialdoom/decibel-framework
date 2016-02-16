<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\packaging;

use app\decibel\application\DApp;
use app\decibel\registry\DClassQuery;

/**
 * Describes a dependency of a %Decibel package on another %Decibel App
 * or a particular version of the framework itself.
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
 * Requiring a specific version (or newer) of the %Decibel framework:
 *
 * @code
 * <dependency type="app\decibel\packaging\DAppDependency">
 *    <name>app\decibel\Decibel</name>
 *    <required>6.3.0</required>
 * </dependency>
 * @endcode
 *
 * Requiring any version of a particular App:
 *
 * @code
 * <dependency type="app\decibel\packaging\DAppDependency">
 *    <name>app\AppNameSpace\AppName</name>
 *    <required>true</required>
 * </dependency>
 * @endcode
 *
 * Requiring a specific version (or newer) of an App:
 *
 * @code
 * <dependency type="app\decibel\packaging\DAppDependency">
 *    <name>app\AppNameSpace\AppName</name>
 *    <required>1.2.0</required>
 * </dependency>
 * @endcode
 *
 * Recommending a specific version (or newer) of an App:
 *
 * @code
 * <dependency type="app\decibel\packaging\DAppDependency">
 *    <name>app\AppNameSpace\AppName</name>
 *    <recommended>1.2.0</recommended>
 * </dependency>
 * @endcode
 *
 * Providing a custom non-compliance message:
 *
 * @code
 * <dependency type="app\decibel\packaging\DAppDependency">
 *    <name>app\AppName\AppName</name>
 *    <recommended>1.2.0</recommended>
 *    <message>\<code\>app\AppNameSpace\AppName\</code\> version 1.2.0 or newer should be installed to enable all
 *    functionality</message>
 * </dependency>
 * @endcode
 *
 * @note
 * The fully qualified name of the App class (which extends
 * {@link app::decibel::application::DApp DApp}) must be used within the
 * <code>\<name\></code> tag.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        packaging
 */
class DAppDependency extends DVersionDependency
{
    /**
     * Qualified name of the App.
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
        return "App <code>{$this->name}</code>";
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
     * Returns the current state of this pre-requisite.
     *
     * @return    mixed
     */
    public function getCurrentState()
    {
        if ($this->currentState) {
            $currentState = $this->currentState;
        } else {
            $qualifiedName = $this->name;
            $validAppClassName = DClassQuery::load()
                                            ->setAncestor(DApp::class)
                                            ->isValid($qualifiedName);
            if (!$validAppClassName) {
                $currentState = false;
            } else {
                /* @var $app DApp */
                $app = new $qualifiedName();
                $currentState = preg_replace('/rc[0-9]+$/', '', $app->getVersion());
            }
        }

        return $currentState;
    }

    /**
     * Returns the qualified name of the App this dependency is for.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->name;
    }
}
