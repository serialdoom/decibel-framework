<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\packaging;

/**
 * Describes a dependency of a %Decibel package on a particular version of MySQL.
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
 * Requiring a particular MySQL version:
 *
 * @code
 * <dependency type="app\decibel\packaging\DMySQLDependency">
 *    <required>5.5.20</required>
 * </dependency>
 * @endcode
 *
 * Recommending a particular MySQL version:
 *
 * @code
 * <dependency type="app\decibel\packaging\DMySQLDependency">
 *    <recommended>5.5.21</recommended>
 * </dependency>
 * @endcode
 *
 * Requiring a particular MySQL version while recommending a newer version:
 *
 * @code
 * <dependency type="app\decibel\packaging\DMySQLDependency">
 *    <required>5.5.20</required>
 *    <recommended>5.5.21</recommended>
 * </dependency>
 * @endcode
 *
 * Providing a custom non-compliance message:
 *
 * @code
 * <dependency type="app\decibel\packaging\DMySQLDependency">
 *    <required>5.5.20</required>
 *    <recommended>5.5.21</recommended>
 *    <message>MySQL version 5.5.20 is required, although 5.5.21 or higher should be installed to ensure maximum
 *    application performance</message>
 * </dependency>
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        packaging
 */
class DMySQLDependency extends DVersionDependency
{
    /**
     * Returns a string describing this DDependency.
     *
     * @return    string
     */
    public function __toString()
    {
        return 'MySQL';
    }

    /**
     * Returns the current state of this pre-requisite.
     *
     * @return    mixed
     */
    public function getCurrentState()
    {
        return $this->currentState
        || function_exists('mysqli_get_client_info');
    }
}
