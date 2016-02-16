<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

use app\decibel\authorise\DUser;
use app\decibel\configuration\DApplicationMode;
use app\decibel\debug\DErrorHandler;
use app\decibel\model\field\DBooleanField;
use app\decibel\rpc\DRemoteProcedure;

/**
 * Generates and returns the %Decibel Debug Console.
 *
 * @section        why Why Would I Use It?
 *
 * The debug console is displayed automatically at the end on an AJAX request,
 * should debug mode be enabled. There is no need to call this remote procedure
 * manually.
 *
 * @section        how How Do I Use It?
 *
 * The remote procedure can be accessed via an AJAX or cURL request.
 * See @ref rpc_executing for more information.
 *
 * @subsection     parameters Parameters
 *
 * - None
 *
 * @subsection     return Return Value
 *
 * - HTML encoded text.
 *
 * @subsection     authorisation Authorisation
 *
 * - None
 *
 * @subsection     example Example
 *
 * The example below will return the debug console.
 *
 * @code
 * http://application.com/remote/decibel/application/DGetDebugConsole
 * @endcode
 *
 * @note
 * The default RPC URL is configurable in %Decibel, and therefore
 * the '/remote/' component of this URL may differ on your %Decibel installation.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        rpc debugging
 */
final class DGetDebugConsole extends DRemoteProcedure
{
    /**
     * Ignore Mode field name.
     *
     * @var        string
     */
    const FIELD_IGNORE_MODE = 'ignoreMode';

    /**
     * Determines if the specified user is authorised to execute
     * this remote procedure call.
     *
     * @param    DUser $user The user to authorise.
     *
     * @return    bool        <code>true</code> if the user is able to execute the procedure,
     *                        <code>false</code> otherwise.
     */
    public function authorise(DUser $user)
    {
        return true;
    }

    /**
     * Determines if the debug console can be shown.
     *
     * @return    bool
     */
    protected function canDebug()
    {
        return DApplicationMode::isProductionMode()
        && (count(DErrorHandler::$debugging) > 0
            || count(DErrorHandler::$profiling) > 0);
    }

    /**
     * Defines the parameters available for this remote procedure.
     *
     * @return    void
     */
    protected function define()
    {
        $ignoreMode = new DBooleanField(self::FIELD_IGNORE_MODE, 'Ignore Mode');
        $ignoreMode->setDefault(false);
        $this->addField($ignoreMode);
    }

    /**
     * Specified the type of result returned by this Remote Procedure.
     *
     * This will ensure the correct MIME type is returned in the HTTP response.
     *
     * @return    string    The result type, must be one of
     *                    {@link DRemoteProcedure::RESULT_TYPE_JSON},
     *                    {@link DRemoteProcedure::RESULT_TYPE_XML},
     *                    {@link DRemoteProcedure::RESULT_TYPE_HTML},
     *                    {@link DRemoteProcedure::RESULT_TYPE_TEXT} or
     *                    {@link DRemoteProcedure::RESULT_TYPE_JAVASCRIPT}.
     */
    public function getResultType()
    {
        return DRemoteProcedure::RESULT_TYPE_HTML;
    }

    /**
     * Determines whether profiling can occur on this remote procedure.
     *
     * @return    bool
     */
    public function profile()
    {
        return false;
    }

    /**
     * Executes the remote procedure and returns the result.
     *
     * @return    string        Result of the procedure.
     */
    public function execute()
    {
        if ($this->canDebug()) {
            // Generate popup and display
            global $output;
            include_once(DECIBEL_PATH . 'app/decibel/_view/debug/DebugConsole.php');
            // Remove reported debug information.
            DErrorHandler::clearDebugging();
        } else {
            $output = null;
        }

        return $output;
    }
}
