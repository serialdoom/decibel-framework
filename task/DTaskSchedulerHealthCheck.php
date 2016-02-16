<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\task;

use app\decibel\health\DHealthCheck;
use app\decibel\health\DHealthCheckResult;
use app\decibel\model\field\DFieldSearch;

/**
 * Checks the health of scheduling functions.
 *
 * @author        Timothy de Paris
 */
class DTaskSchedulerHealthCheck extends DHealthCheck
{
    /**
     * Performs the health check.
     *
     * @return    array    List of {@link app::decibel::health::DHealthCheckResult DHealthCheckResult}
     *                    objects.
     */
    public function checkHealth()
    {
        $results = array();
        // Make sure there are tasks scheduled.
        if (!DTaskSchedule::search()->hasResults()) {
            $results[] = new DHealthCheckResult(
                DHealthCheckResult::HEALTH_CHECK_ERROR,
                'There are no regular tasks scheduled. Check that the cron task is configured for this installation.'
            );
            // If so, find long running tasks.
        } else {
            $this->checkLongRunningTasks($results);
        }

        return $results;
    }

    /**
     * Checks for issues with long running tasks.
     *
     * @param    array $results           List of results in which any additional
     *                                    results will be added.
     *
     * @return    void
     */
    protected function checkLongRunningTasks(array &$results)
    {
        $now = time();
        $thirtyMinutes = 1800;
        $longRunning = DTaskSchedule::search()
                                    ->filterByField(DTaskSchedule::FIELD_SCHEDULED, $now - $thirtyMinutes,
                                                    DFieldSearch::OPERATOR_LESS_THAN)
                                    ->getFields(array(
                                                    DTaskSchedule::FIELD_TASK,
                                                    DTaskSchedule::FIELD_SCHEDULED,
                                                    DTaskSchedule::FIELD_STARTED,
                                                ));
        foreach ($longRunning as $taskDetails) {
            $this->checkLongRunningTask($taskDetails, $results);
        }
    }

    /**
     * Checks a long running task and adds the relevant health check result.
     *
     * @param    array $taskDetails       Details of the long running task.
     * @param    array $results           List of results in which any additional
     *                                    results will be added.
     *
     * @return    void
     */
    protected function checkLongRunningTask(array $taskDetails, array &$results)
    {
        $qualifiedName = $taskDetails[ DTaskSchedule::FIELD_TASK ];
        // If task is running for more than 30 minutes
        if ($taskDetails[ DTaskSchedule::FIELD_STARTED ] !== null) {
            $results[] = new DHealthCheckResult(
                DHealthCheckResult::HEALTH_CHECK_WARNING,
                "Task {$qualifiedName} has been running for more than 30 minutes. Start time: " . date('d/m/Y \a\t H:m',
                                                                                                       $taskDetails[ DTaskSchedule::FIELD_SCHEDULED ])
            );
            // If task does not run in last 30 minutes
        } else {
            // Ignore tasks that can't run in the current mode.
            /* @var $task DTask */
            $task = new $qualifiedName();
            if ($task->canRunInMode()) {
                $results[] = new DHealthCheckResult(
                    DHealthCheckResult::HEALTH_CHECK_ERROR,
                    "Task {$qualifiedName} is more than 30 minutes overdue. Scheduled time: " . date('d/m/Y \a\t H:m',
                                                                                                     $taskDetails[ DTaskSchedule::FIELD_SCHEDULED ])
                );
            }
        }
    }

    /**
     * Returns the name of the component being checked.
     *
     * @return    string
     */
    public function getComponentName()
    {
        return 'Task Scheduler';
    }
}
