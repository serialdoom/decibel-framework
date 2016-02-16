<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\utilisation;

use app\decibel\debug\DErrorHandler;
use app\decibel\model\DModel;
use app\decibel\model\event\DOnSave;
use app\decibel\task\DScheduledTask;
use Exception;

/**
 * Rebuilds the Utilisation Index.
 *
 * @author        Timothy de Paris
 */
class DRebuildUtilisationIndex extends DScheduledTask
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
        // Clean the utilistion index.
        DUtilisationRecord::cleanDatabase();
        // Determine objects that are searchable.
        $models = DModel::search()
                        ->removeDefaultFilters();
        // Track progress of task execution.
        $totalObjects = $models->getCount();
        $currentObject = 0;
        foreach ($models as $model) {
            /* @var $model DModel */
            try {
                $event = new DOnSave();
                $event->setFieldValue('dispatcher', $model);
                DUtilisationRecord::index($event);
                $model->free();
                // Catch and report any exception to allow processing to continue.
            } catch (Exception $exception) {
                DErrorHandler::logException($exception);
            }
            // Update progress of task execution.
            ++$currentObject;
            $this->updateProgress($currentObject, $totalObjects);
        }
    }
}
