<?php

// bailout if the PHP version is below PHP 5.5
if (version_compare(PHP_VERSION, '5.5', '<')) {
    trigger_error('Your PHP version (' . PHP_VERSION . ') is too old', E_USER_ERROR);
}

//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//

//
// @deprecated
//
// This file exists because of compatibility reasons with previous versions and shell scripts
// use Composer Autoloading going forward
//

use app\decibel\application\DAppManager;
use app\decibel\application\DConfigurationManager;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DProfiler;
use app\decibel\file\DFile;
use app\decibel\router\DRouter;

//
// Constants
//

/** @var float Time the bootstrap was initialised for profiling */
define('DECIBEL_START', microtime(true));

if (!defined('DECIBEL_PATH')) {
    /** @var string The absolute path to the application on the server, contains
     *              a DIRECTORY_SEPARATOR at the end of the directory path */
    $path = str_replace('\\', DIRECTORY_SEPARATOR, dirname(dirname(dirname(__DIR__))));
    define('DECIBEL_PATH', $path . ($path[ strlen($path) - 1 ] == DIRECTORY_SEPARATOR
            ? '' : DIRECTORY_SEPARATOR)
    );
}
chdir(DECIBEL_PATH);

/** @var string */
define('NAMESPACE_SEPARATOR', '\\');

// todo: decrease this mess by using DI / abstract away

/** @var string */
define('DECIBEL_LOGS_DIR', DECIBEL_PATH . sprintf('var%1$slogs%1$s', DIRECTORY_SEPARATOR));
/** @var string */
define('TEMP_PATH',    DECIBEL_PATH . '_temp' . DIRECTORY_SEPARATOR);
/** @var string */
define('PACKAGE_PATH', DECIBEL_PATH . '_packages' . DIRECTORY_SEPARATOR);
/** @var string */
define('CONFIG_PATH',  DECIBEL_PATH . '_config' . DIRECTORY_SEPARATOR);
/** @var string */
define('SESSION_PATH', DECIBEL_PATH . sprintf('var%1$scache%1$s', DIRECTORY_SEPARATOR));

/** @var boolean indicating whether composer is available */
define('DECIBEL_COMPOSER_ENABLED', !!file_exists('./vendor/autoload.php'));

//
// Setup environment
//

if (!class_exists('\PHPUnit_Framework_TestCase')) {
    class PHPUnit_Framework_TestCase
    { }
}

// Turn strict error handling off until custom error handler can be used.
ini_set('error_reporting', E_ALL & ~E_STRICT & ~E_DEPRECATED);
ini_set('error_log', DECIBEL_LOGS_DIR); // Force error logs into common location.

if (DECIBEL_COMPOSER_ENABLED) {
    // load autoloader
    require './vendor/autoload.php';

    // $dotEnv = new Dotenv\Dotenv(DECIBEL_PATH);
    // $dotEnv->load();

// Registers the custom autoloader if composer is not available
} else {

    // Setup available files array for autoloader optimisation.
    $GLOBALS['availableFiles'] = array();

    // Include the minimum classes needed to boot Decibel before
    // the global DAppInformaton registry is loaded. It is more
    // efficient to manually load them, as otherwise a file_exists
    // call will be made by Decibel's autoloader.
    // @note       Order is important!
    // @todo       These need to be removed as the classes are
    //                     moved into the Decibel PHP extension, and should
    //                     be reviewed for currency from time to time using
    //                     the following code segment before the file_exists
    //                     call in decibelAutoLoader:
    //                     if (empty($GLOBALS['availableFiles'])) {
    //                             echo "include_once(DECIBEL_PATH . '{$relativeFilename}');\n";
    //                     }
    include_once('./app/decibel/decorator/DDecoratable.php');
    include_once('./app/decibel/decorator/DDecoratorCache.php');
    include_once('./app/decibel/utility/DBaseClass.php');
    include_once('./app/decibel/event/DDispatchable.php');
    include_once('./app/decibel/event/DEventDispatcher.php');
    include_once('./app/decibel/debug/DProfiler.php');
    include_once('./app/decibel/debug/DBasicProfiler.php');
    include_once('./app/decibel/utility/DSingleton.php');
    include_once('./app/decibel/utility/DSingletonClass.php');
    include_once('./app/decibel/application/DConfigurationManager.php');
    include_once('./app/decibel/debug/DDebuggable.php');
    include_once('./app/decibel/utility/DDefinableCache.php');
    include_once('./app/decibel/utility/DDefinableObject.php');
    include_once('./app/decibel/utility/DDefinable.php');
    include_once('./app/decibel/utility/DPharHive.php');
    include_once('./app/decibel/configuration/DConfiguration.php');
    include_once('./app/decibel/configuration/DApplicationMode.php');
    include_once('./app/decibel/utility/DList.php');
    include_once('./app/decibel/debug/DDebuggable.php');
    include_once('./app/decibel/registry/DClassQuery.php');
    include_once('./app/decibel/cache/DCacheHandler.php');
    include_once('./app/decibel/regional/DLabel.php');
    include_once('./app/decibel/application/DAppManager.php');
    include_once('./app/decibel/event/DEventSubscription.php');
    include_once('./app/decibel/configuration/DConfigurable.php');
    include_once('./app/decibel/service/DServiceContainer.php');
    include_once('./app/decibel/adapter/DAdaptable.php');
    include_once('./app/decibel/adapter/DAdapterCache.php');
    include_once('./app/decibel/cache/DCache.php');
    include_once('./app/decibel/cache/DPublicCache.php');
    include_once('./app/decibel/file/DFile.php');
    include_once('./app/decibel/debug/DErrorHandler.php');

    include_once('./app/decibel/helpers.php');

    /**
     * Loads class definitions as required.
     *
     * @param string $className The name of the missing class.
     *
     * @return bool
     */
    function decibelAutoloader($className)
    {
        if (strpos($className, 'app' . NAMESPACE_SEPARATOR) === false) {
            return false;
        }

        // Determine file name for this class.
        $filename = str_replace(NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR,
                                $className) . '.php';
        // Check in the availableFiles array (if it has been set yet).
        // Note: isset is much, much faster than in_array
        if (isset($GLOBALS['availableFiles'][ $filename ])
            // Check on the file system.
            || file_exists($filename)
        ) {
            include_once($filename);
            return true;
        }

        return false;
    }

    spl_autoload_register('decibelAutoloader');
}

define('DECIBEL_REGISTRY_PATH', DFile::correctSlashFor('var/cache/'));

//
// Actual bootstrap
//

// set the $startTime variable for compat reasons
// @deprecated, use DECIBEL_START instead
$startTime = value(function() {
    $parts = explode('.', DECIBEL_START);
    return sprintf('%s %s',
                   str_pad(($parts[1] / 10000) . '', 10, '0'),
                   $parts[0]);
});

// Load the profiler.
$profiler = DProfiler::load();
$profiler->start($startTime);
if (defined(DProfiler::PROFILER_ENABLED)) {
    $profiler->startActivity('Decibel::startup', $startTime);
}

// Load configuration and App information.
// Load sequence is important:
// 1) Load the configuration manager, in order to load the correct application mode.
// 2) Load the app manager, so we know which Apps are installed.
$configurationManager = DConfigurationManager::load();
$appManager = DAppManager::load();
// Load error handling functionality.
DErrorHandler::load();
// Route the request.
DRouter::getRouter()
       ->execute();
