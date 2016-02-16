<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

use app\decibel\configuration\DApplicationMode;

/**
 * Handles an exception occurring when a deprecated feature is used.
 *
 * @section        why Why Would I Use It?
 *
 * This exception should be thrown whenever a deprecated feature
 * of an App is used.
 *
 * See @ref debugging_exceptions_deprecated for further information.
 *
 * @section        how How Do I Use It?
 *
 * The exception should be thrown using the {@link DErrorHandler::throwException()}
 * function. When creating the exception, the name of the deprecated feature
 * and the replacement feature should be passed as parameters.
 *
 * @note
 * Using the {@link DErrorHandler::throwException()} function ensures that
 * this exception will not interrupt application execution when %Decibel
 * is running in @ref configuration_mode_production.
 * See @ref debugging_exceptions_throwing for further information.
 *
 * @subsection     example Examples
 *
 * The following example throws an exception showing that the function
 * <code>deprecatedFeature()</code> has been deprecated and replaced
 * by the <code>replacementFeature()</code> function:
 *
 * @code
 * use app\decibel\debug\DDeprecatedException;
 * use app\decibel\debug\DErrorHandler;
 *
 * public function deprecatedFeature() {
 *    DErrorHandler::throwException(
 *        new DDeprecatedException(
 *            'deprecatedFeature()',
 *            'replacementFeature()'
 *        )
 *    );
 * }
 * @endcode
 *
 * This will generate the following message:
 *
 * <em><code>deprecatedFeature()</code> has been deprecated in favour of <code>replacementFeature()</code></em>
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        debugging_special
 */
class DDeprecatedException extends DException
{
    /**
     * Creates a new {@link DDeprecatedException}.
     *
     * @param    string $deprecated  Description of the deprecated feature.
     * @param    string $replacement Description of the replacement feature.
     *
     * @return    static
     */
    public function __construct($deprecated, $replacement)
    {
        parent::__construct(array(
                                'deprecated'  => $deprecated,
                                'replacement' => $replacement,
                            ));
    }

    /**
     * Specifies whether it is possible for the application to recover from
     * this type of exception and continue execution.
     *
     * @return    bool
     */
    public function isRecoverable()
    {
        return !DApplicationMode::isDebugMode();
    }
}
