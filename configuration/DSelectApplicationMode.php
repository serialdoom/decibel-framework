<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\configuration;

use app\decibel\application\DConfigurationManager;
use app\decibel\model\field\DEnumStringField;
use app\decibel\regional\DLabel;
use app\decibel\rpc\DRemoteProcedure;
use app\decibel\utility\DJson;
use app\decibel\utility\DResult;

/**
 * Allows selection of the Application Mode to be used by decibel.
 *
 * See the @ref configuration_mode Developer Guide for further information about
 * Application Modes.
 *
 * @section        why Why Would I Use It?
 *
 * This remote procedure can be used to remotely change the Application Mode
 * used by %Decibel.
 *
 * @section        how How Do I Use It?
 *
 * The remote procedure can be accessed via an AJAX or cURL request.
 * See @ref rpc_executing for more information.
 *
 * @subsection     parameters Parameters
 *
 * - <code>mode</code>: The Application Mode to use. Must be one of
 *    {@link app::decibel::configuration::DApplicationMode::MODE_DEBUG DApplicationMode::MODE_DEBUG},
 *    {@link app::decibel::configuration::DApplicationMode::MODE_TEST DApplicationMode::MODE_TEST},
 *    {@link app::decibel::configuration::DApplicationMode::MODE_PRODUCTION DApplicationMode::MODE_PRODUCTION}
 *
 * @subsection     return Return Value
 *
 * - A JSON encoded {@link app::decibel::utility::DResult DResult} object, or
 * - A JSON encoded {@link app::decibel::rpc::debug::DMissingRpcParameterException DMissingRpcParameterException}
 *        if no <code>mode</code> was provided, or
 * - A JSON encoded {@link app::decibel::rpc::debug::DInvalidRpcParameterException DInvalidRpcParameterException}
 *        if an invalid <code>mode</code> was provided.
 *
 * @subsection     authorisation Authorisation
 *
 * - The user must have root privileges.
 *
 * @subsection     example Example
 *
 * The example below will configure %Decibel to use Production mode.
 *
 * @code
 * http://application.com/remote/decibel/configuration/DSelectApplicationMode
 *        ?mode=production
 * @endcode
 *
 * @note
 * The default RPC URL is configurable in %Decibel, and therefore
 * the '/remote/' component of this URL may differ on your %Decibel installation.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        rpc configuration
 */
class DSelectApplicationMode extends DRemoteProcedure
{
    /**
     * 'Mode' field name.
     *
     * @var        string
     */
    const FIELD_MODE = 'mode';

    /**
     * Defines the parameters available for this remote procedure.
     *
     * @return    void
     */
    protected function define()
    {
        $mode = new DEnumStringField(self::FIELD_MODE,
                                     new DLabel(DConfigurationManager::class, 'applicationMode'));
        $mode->setValues(DApplicationMode::getAvailableModes());
        $mode->setRequired(true);
        $this->addField($mode);
    }

    /**
     * Executes the remote procedure and returns the result.
     *
     * @return    string        Result of the procedure.
     */
    public function execute()
    {
        $mode = $this->getFieldValue(self::FIELD_MODE);
        $result = new DResult(
            $this->getField(self::FIELD_MODE)->toString($mode),
            new DLabel('app\\decibel', 'selected')
        );
        // Select application mode.
        $result->setSuccess(
            DApplicationMode::setMode($mode)
        );

        return DJson::encode($result);
    }
}
