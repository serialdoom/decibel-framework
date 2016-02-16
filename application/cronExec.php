<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
use app\decibel\authorise\DAuthorisationManager;
use app\decibel\authorise\DRootUser;
use app\decibel\debug\DProfiler;
use app\decibel\regional\DLanguage;
use app\decibel\task\DTaskSchedule;

define('PATH', dirname(__FILE__) . '/../../../');

// Include application essentials.
include_once(PATH . 'app/decibel/application/bootstrap.php');
// Attempt to login as the built-in root user.
$cronUser = DRootUser::create();
$authorisationManager = DAuthorisationManager::load();
$authorisationManager->login($cronUser);
// Initialise the language.
DLanguage::initialise();
DTaskSchedule::runScheduledTasks();
// Stop the profiler.
if (defined(DProfiler::PROFILER_ENABLED)) {
    DProfiler::load()->stop();
}
