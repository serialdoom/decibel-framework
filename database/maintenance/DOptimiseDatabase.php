<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\maintenance;

use app\decibel\database\DDatabase;
use app\decibel\database\utility\DDatabaseOptimiseUtility;
use app\decibel\task\DScheduledTask;

/**
 * Optimises the %Decibel database and triggers any custom optimisation
 * functionality within installed Apps.
 *
 * @section        why Why Would I Use It?
 *
 * This task can be used to clean up redundant information in the %Decibel
 * database following application development, or during regular application
 * maintenance.
 *
 * When executed, this scheduled task performs two tasks:
 * - Executes the {@link DDatabase::optimise()} function on the application database controller.
 * - Triggers the <code>app\\decibel\\database\\DDatabase-optimise</code> task.
 *
 * @section        how How Do I Use It?
 *
 * See the @ref performance_optimisation Developer Guide for detailed
 * usage information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        database_events
 */
class DOptimiseDatabase extends DScheduledTask
{
    /**
     * Executes the task.
     *
     * This function will be called by the task scheduler whenever the
     * task is scheduled to run.
     *
     * @return    void
     */
    protected function execute()
    {
        $this->updateProgress(1, 10);
        // Run database optimise task.
        $database = DDatabase::getDatabase();
        $dbUtilities = DDatabaseOptimiseUtility::adapt($database);
        $dbUtilities->optimise();
        $this->updateProgress(10, 10);
    }
}
