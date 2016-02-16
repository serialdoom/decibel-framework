<?php
/**
 * Copyright (c) 2008-2016 Decibel Technology Limited.
 *
 * Auto-loads the Testing environment for PHPUnit.
 *
 * Tests should not be dependent on Registry configuration, therefore eliminating
 * the need for registry initialisation eliminating the need for a registry
 * compatible file structure.
 *
 * @author Alex van Andel <avanandel@decibeltechnology.com>
 */
require_once __DIR__ . '/../vendor/autoload.php';

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
use app\decibel\registry\DGlobalRegistry;
use app\decibel\router\DRouter;

//
// Constants
//

/** @var float Time the bootstrap was initialised for profiling */
define('DECIBEL_START', microtime(true));
define('DECIBEL_COMPOSER_ENABLED', true);

/** @var string The absolute path to the application on the server, contains
 *              a DIRECTORY_SEPARATOR at the end of the directory path */
// define('DECIBEL_PATH', dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . DIRECTORY_SEPARATOR);
define('DECIBEL_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

chdir(DECIBEL_PATH);

/** @var string */
define('NAMESPACE_SEPARATOR', '\\');

define('DECIBEL_REGISTRY_PATH', '_build/');

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

//
//
//
putenv('APP_ENV=staging');
putenv('DB_HOSTNAME=mysql.local');
putenv('DB_DATABASE=decibel_dev');
putenv('DB_USERNAME=admin');
putenv('DB_PASSWORD=AVAchjWQ');
