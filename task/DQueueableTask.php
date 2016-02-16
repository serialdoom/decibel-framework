<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

use app\decibel\authorise\DAuthorisationManager;
use app\decibel\debug\DErrorHandler;
use app\decibel\task\DOnTaskExecute;
use app\decibel\task\DQueueable;
use app\decibel\task\DTask;
use app\decibel\utility\DDefinable;
use Exception;

/**
 * Base class for all queueable tasks.
 *
 * @author         Timothy de Paris
 * @ingroup        tasks_queued
 */
abstract class DQueueableTask extends DTask implements DQueueable, DDefinable
{
    /**
     * Reference to the qualified name of the
     * {@link app::decibel::task::DOnTaskQueue DOnTaskQueue}
     * event.
     *
     * @var        string
     */
    const ON_QUEUE = DOnTaskQueue::class;

    /**
     * Options set for this task execution.
     *
     * @var        mixed
     */
    protected $options;

    /**
     * Returns names of the events produced by this dispatcher.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents()
    {
        $events = parent::getEvents();
        $events[] = self::ON_QUEUE;

        return $events;
    }

    /**
     * Runs the task.
     *
     * @param    mixed $options Task options.
     *
     * @return    void
     */
    public function run($options = null)
    {
        // Ignore script execution time limit.
        set_time_limit(0);
        ob_start();
        try {
            $this->options = $options;
            $this->markStarted();
            $this->execute();
            // Trigger onExecute event.
            $event = new DOnTaskExecute();
            $this->notifyObservers($event);
            $this->finalise();
        } catch (Exception $e) {
            DErrorHandler::throwException($e);
        }
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Finalises the run process.
     * Could be overriden in other types of tasks.
     *
     * @return    void
     */
    public function finalise()
    {
        // Delete the task from the schedule table
        $this->cancel();
    }

    /**
     * Updates the progress of the task.
     *
     * @param    int $currentStep         The current step of task execution.
     * @param    int $totalSteps          The total number of steps required to
     *                                    execute the task.
     *
     * @return    void
     */
    protected function updateProgress($currentStep, $totalSteps)
    {
        // Calculate new progress.
        $progress = ceil(($currentStep / $totalSteps) * 100);
        // If changed since the last update, store in the database.
        if ($progress != $this->progress) {
            $user = DAuthorisationManager::getUser();
            $this->queue->setFieldValue(DTaskSchedule::FIELD_PROGRESS, $progress);
            $this->queue->save($user);
        }
        // Store new progress.
        $this->progress = $progress;
    }

    /**
     * Cancels this task.
     *
     * @return    bool
     */
    public function cancel()
    {
        return false;
    }

    /**
     * Marks a task as started.
     *
     * @return    void
     */
    private function markStarted()
    {
    }

    /**
     * Determines the status of a specified task.
     *
     * @return    mixed    <code>false</code> if the task has not been scheduled,
     *                    the timestamp at which the task is scheduled,
     *                    or <code>true</code> if the task is in progress.
     */
    public function getStatus()
    {
    }
}
