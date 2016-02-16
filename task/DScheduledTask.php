<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

use app\decibel\authorise\DAuthorisationManager;
use app\decibel\debug\DErrorHandler;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\server\DServer;
use Exception;

/**
 * Base class for all scheduled tasks.
 *
 * @author         Timothy de Paris
 * @ingroup        tasks
 */
abstract class DScheduledTask extends DTask
{
    /**
     * Reference to the qualified name of the
     * {@link app::decibel::task::DOnTaskSchedule DOnTaskSchedule}
     * event.
     *
     * @var        string
     */
    const ON_SCHEDULE =  DOnTaskSchedule::class;

    /**
     * A task schedule representing the current scheduled state of this task.
     *
     * @var        DTaskSchedule
     */
    protected $schedule;

    /**
     * Creates a new {@link DScheduledTask} instance.
     *
     * @return    static
     */
    public function __construct()
    {
        $this->schedule = DTaskSchedule::search()
                                       ->filterByField(DTaskSchedule::FIELD_TASK, get_class($this))
                                       ->getObject();
        if ($this->schedule === null) {
            $this->schedule = DTaskSchedule::create(array(
                                                        DTaskSchedule::FIELD_TASK => get_class($this),
                                                    ));
        }
    }

    /**
     * Audits an action for this task.
     *
     * @param    int $action      The action to audit. One of:
     *                            - {@link DTaskLog::ACTION_SCHEDULED}
     *                            - {@link DTaskLog::ACTION_RESCHEDULED}
     *                            - {@link DTaskLog::ACTION_EXECUTED}
     *                            - {@link DTaskLog::ACTION_CANCELLED}
     *                            - {@link DTaskLog::ACTION_FAILED}
     *
     * @return    void
     */
    final protected static function audit($action)
    {
        $auditable = static::getAuditableActions();
        if (in_array($action, $auditable)) {
            DTaskLog::log(array(
                              DTaskLog::FIELD_TASK   => get_called_class(),
                              DTaskLog::FIELD_ACTION => $action,
                          ));
        }
    }

    /**
     * Determines whether this task is able to be forced to run through the
     * administration interface.
     *
     * @return    bool
     */
    public function canBeForced()
    {
        return true;
    }

    /**
     * Returns a list of actions for which this task will be audited.
     *
     * By default, tasks will be auditing for
     * {@link app::decibel::task::DTaskLog::ACTION_SCHEDULED DTaskLog::ACTION_SCHEDULED},
     * {@link app::decibel::task::DTaskLog::ACTION_CANCELLED DTaskLog::ACTION_CANCELLED} and
     * {@link app::decibel::task::DTaskLog::ACTION_FAILED DTaskLog::ACTION_FAILED} actions.
     * Override this function in extending classes to modify the actions that
     * a particular class will be audited for.
     *
     * @return    array    List including zero or more of:
     *                    - {@link app::decibel::task::DTaskLog::ACTION_SCHEDULED DTaskLog::ACTION_SCHEDULED}
     *                    - {@link app::decibel::task::DTaskLog::ACTION_RESCHEDULED DTaskLog::ACTION_RESCHEDULED}
     *                    - {@link app::decibel::task::DTaskLog::ACTION_EXECUTED DTaskLog::ACTION_EXECUTED}
     *                    - {@link app::decibel::task::DTaskLog::ACTION_CANCELLED DTaskLog::ACTION_CANCELLED}
     *                    - {@link app::decibel::task::DTaskLog::ACTION_FAILED DTaskLog::ACTION_FAILED}
     */
    public static function getAuditableActions()
    {
        return array(
            DTaskLog::ACTION_SCHEDULED,
            DTaskLog::ACTION_CANCELLED,
            DTaskLog::ACTION_FAILED,
        );
    }

    /**
     * Returns names of the events produced by this dispatcher.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents()
    {
        $events = parent::getEvents();
        $events[] = DScheduledTask::ON_SCHEDULE;

        return $events;
    }

    /**
     * Runs the task.
     *
     * @return    void
     */
    public function run()
    {
        // Ignore script execution time limit.
        set_time_limit(0);
        // Start an outout buffer in case anything is echoed.
        ob_start();
        try {
            $this->markStarted();
            $this->execute();
            static::audit(DTaskLog::ACTION_EXECUTED);
            // Trigger onExecute event.
            $event = new DOnTaskExecute();
            $this->notifyObservers($event);
            $this->finalise();
        } catch (Exception $e) {
            // Log the exception.
            DErrorHandler::throwException($e);
            static::audit(DTaskLog::ACTION_FAILED);
            // Trigger onFail event.
            $event = new DOnTaskFail();
            $this->notifyObservers($event);
            $this->finalise();
        }
        // Clear the output buffer and return it's content.
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
        if ($totalSteps === 0) {
            return;
        }
        // Calculate new progress.
        $progress = ceil(($currentStep / $totalSteps) * 100);
        // If changed since the last update, store in the database.
        if ($progress !== $this->progress) {
            $user = DAuthorisationManager::getUser();
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_PROGRESS, $progress);
            $this->schedule->save($user);
        }
        // Store new progress.
        $this->progress = $progress;
    }

    /**
     * Schedules this task for execution.
     *
     * @param    int  $when           Timestamp representing the time at which
     *                                the task is to be executed.
     * @param    bool $forced         Whether the task has been forced to run
     *                                through the administration interface.
     *
     * @return    bool
     */
    public function schedule($when, $forced = false)
    {
        try {
            $user = DAuthorisationManager::getUser();
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_OWNER, DAuthorisationManager::getResponsibleUser());
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_SCHEDULED, $when);
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_FORCED, $forced);
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_STARTED, null);
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_PROCESS_ID, null);
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_PROGRESS, 0);
            $this->schedule->save($user);
            static::audit(DTaskLog::ACTION_SCHEDULED);
            // Trigger onSchedule event.
            $event = new DOnTaskSchedule();
            $this->notifyObservers($event);
            $success = true;
        } catch (DInvalidFieldValueException $exception) {
            DErrorHandler::throwException($exception);
            $success = false;
        }

        return $success;
    }

    /**
     * Reschedules this task for execution.
     *
     * @param    int  $when       Timestamp representing the new time at which
     *                            the task is to be executed.
     * @param    bool $forced     Whether the task has been forced to run
     *                            through the administration interface.
     *
     * @return    bool
     */
    public function reschedule($when, $forced = false)
    {
        try {
            $user = DAuthorisationManager::getUser();
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_OWNER, DAuthorisationManager::getResponsibleUser());
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_SCHEDULED, $when);
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_FORCED, $forced);
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_STARTED, null);
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_PROCESS_ID, null);
            $this->schedule->setFieldValue(DTaskSchedule::FIELD_PROGRESS, 0);
            $this->schedule->save($user);
            static::audit(DTaskLog::ACTION_RESCHEDULED);
            // Trigger onSchedule event.
            $event = new DOnTaskSchedule();
            $this->notifyObservers($event);
            $success = true;
        } catch (DInvalidFieldValueException $exception) {
            DErrorHandler::throwException($exception);
            $success = false;
        }

        return $success;
    }

    /**
     * Cancels this task.
     *
     * @return    bool
     */
    public function cancel()
    {
        $user = DAuthorisationManager::getUser();
        $result = DTaskSchedule::search()
                               ->filterByField(DTaskSchedule::FIELD_TASK, get_class($this))
                               ->delete($user);
        // If no records were modified,
        // there was no task to cancel.
        if (!$result) {
            $success = false;
        } else {
            static::audit(DTaskLog::ACTION_CANCELLED);
            // Trigger onCancel event.
            $event = new DOnTaskCancel();
            $this->notifyObservers($event);
            $success = true;
        }

        return $success;
    }

    /**
     * Marks a task as started.
     *
     * @return    void
     */
    private function markStarted()
    {
        $user = DAuthorisationManager::getUser();
        $server = DServer::load();
        $this->schedule->setFieldValue(DTaskSchedule::FIELD_STARTED, time());
        $this->schedule->setFieldValue(DTaskSchedule::FIELD_PROCESS_ID, $server->getProcessId());
        $this->schedule->setFieldValue(DTaskSchedule::FIELD_PROGRESS, 0);
        $this->schedule->save($user);
    }

    /**
     * Determines the status of a task.
     *
     * @return    DTaskStatus        The task status, or <code>null</code> if the task
     *                            has not been scheduled.
     */
    public static function getStatus()
    {
        $qualifiedName = get_called_class();
        $schedule = DTaskSchedule::search()
                                 ->filterByField(DTaskSchedule::FIELD_TASK, $qualifiedName)
                                 ->getObject();
        if ($schedule === null) {
            $status = null;
        } else {
            $status = new DTaskStatus($schedule);
        }

        return $status;
    }
}
