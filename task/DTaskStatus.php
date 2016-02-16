<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

use app\decibel\authorise\DUser;
use app\decibel\model\field\DBooleanField;
use app\decibel\model\field\DDateTimeField;
use app\decibel\model\field\DFloatField;
use app\decibel\model\field\DIntegerField;
use app\decibel\model\field\DLinkedObjectField;
use app\decibel\model\field\DQualifiedNameField;
use app\decibel\regional\DLabel;
use app\decibel\utility\DUtilityData;

/**
 * Provides information about the status of a particular
 * {@link app::decibel::task::DScheduledTask DScheduledTask}.
 *
 * @author         Timothy de Paris
 * @ingroup        tasks
 */
class DTaskStatus extends DUtilityData
{
    /** @var string 'Initiating' field name. */
    const FIELD_INITIATING = 'initiating';

    /** @var string 'Owner' field name. */
    const FIELD_OWNER = 'owner';

    /** @var string 'Pending' field name. */
    const FIELD_PENDING = 'pending';

    /** @var string 'Process ID' field name. */
    const FIELD_PROCESS_ID = 'processId';

    /** @var string 'Progress' field name. */
    const FIELD_PROGRESS = 'progress';

    /** @var string 'Running' field name. */
    const FIELD_RUNNING = 'running';

    /** @var string 'Scheduled' field name. */
    const FIELD_SCHEDULED = 'scheduled';

    /** @var string 'Task' field name. */
    const FIELD_TASK = 'task';

    /**
     * A list of error messages describing why the task cannot be executed,
     * if this is the case.
     *
     * @var        array
     */
    protected $errors = array();

    /**
     * Create a new DTaskStatus object.
     *
     * @param    DTaskSchedule $schedule Scheduling information for the task.
     *
     * @return    static
     */
    public function __construct(DTaskSchedule $schedule)
    {
        parent::__construct();
        $task = $schedule->getFieldValue(DTaskSchedule::FIELD_TASK);
        $started = $schedule->getFieldValue(DTaskSchedule::FIELD_STARTED);
        $progress = $schedule->getFieldValue(DTaskSchedule::FIELD_PROGRESS);
        $this->setFieldValue(self::FIELD_TASK, $task);
        $this->setFieldValue(self::FIELD_OWNER, $schedule->getFieldValue(DTaskSchedule::FIELD_OWNER));
        $this->setFieldValue(self::FIELD_SCHEDULED, $schedule->getFieldValue(DTaskSchedule::FIELD_SCHEDULED));
        $this->setFieldValue(self::FIELD_PENDING, !$started);
        $this->setFieldValue(self::FIELD_INITIATING, ($started && $progress === 0));
        $this->setFieldValue(self::FIELD_RUNNING, ($started && $progress > 0));
        $this->setFieldValue(self::FIELD_PROGRESS, $progress);
        $this->setFieldValue(self::FIELD_PROCESS_ID, $schedule->getFieldValue(DTaskSchedule::FIELD_PROCESS_ID));
        // Check for any issues that may stop the task running.
        $taskInstance = new $task();
        if (!$taskInstance->canRunInMode()) {
            $this->addErrorMessage(new DLabel(
                                       DScheduledTask::class,
                                       'errorMode'
                                   ));
        }
    }

    /**
     * Adds an error message to the status of this task.
     *
     * Error messages describe why a task cannot currently be executed.
     *
     * @param    DLabel $message The error message to add.
     *
     * @return    static
     */
    public function addErrorMessage(DLabel $message)
    {
        $this->errors[] = $message;

        return $this;
    }

    /**
     * Defines fields available for this object.
     *
     * @return    void
     */
    protected function define()
    {
        $labelUnknown = new DLabel('app\\decibel', 'unknown');
        $task = new DQualifiedNameField(self::FIELD_TASK, 'Task');
        $task->setAncestors(array(DScheduledTask::class));
        $task->setRequired(true);
        $this->addField($task);
        $scheduled = new DDateTimeField(self::FIELD_SCHEDULED, 'Scheduled Time');
        $scheduled->setNullOption($labelUnknown);
        $this->addField($scheduled);
        $owner = new DLinkedObjectField(self::FIELD_OWNER, 'Owner');
        $owner->setLinkTo(DUser::class);
        $owner->setNullOption($labelUnknown);
        $this->addField($owner);
        $pending = new DBooleanField(self::FIELD_PENDING, 'Pending');
        $this->addField($pending);
        $initiating = new DBooleanField(self::FIELD_INITIATING, 'Initiating');
        $this->addField($initiating);
        $running = new DBooleanField(self::FIELD_RUNNING, 'Running');
        $this->addField($running);
        $progress = new DFloatField(self::FIELD_PROGRESS, 'Progress');
        $this->addField($progress);
        $processId = new DIntegerField(self::FIELD_PROCESS_ID, 'Process ID');
        $processId->setNullOption($labelUnknown);
        $this->addField($processId);
    }

    /**
     * Returns any error message describing the current state of the task.
     *
     * @return    array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Returns the user that scheduled the task.
     *
     * @return    DUser
     */
    public function getOwner()
    {
        return $this->getFieldValue(self::FIELD_OWNER);
    }

    /**
     * Returns the current progress of the task.
     *
     * @return    float    Percentage progress of the task.
     */
    public function getProgress()
    {
        return $this->getFieldValue(self::FIELD_PROGRESS);
    }

    /**
     * Returns the time that the task is scheduled to be run.
     *
     * @return    int        UNIX timestamp, or <code>null</code> if the task
     *                    is not currently scheduled.
     */
    public function getScheduledTime()
    {
        return $this->getFieldValue(self::FIELD_SCHEDULED);
    }

    /**
     * Returns the qualified name of the task.
     *
     * @return    string
     */
    public function getTask()
    {
        return $this->getFieldValue(self::FIELD_TASK);
    }

    /**
     * Determines the type of task represented by this object.
     *
     * @param    string $task Qualified name of the task.
     *
     * @return    string    Qualified name of the parent class that represents
     *                    the task type, one of:
     *                    - {@link DNightlyTask::class}
     *                    - {@link DRegularTask::class}
     *                    - {@link DScheduledTask::class}
     */
    public function getType($task)
    {
        $parents = class_parents($task);
        if (in_array(DNightlyTask::class, $parents)) {
            $type = DNightlyTask::class;
        } else {
            if (in_array(DRegularTask::class, $parents)) {
                $type = DRegularTask::class;
            } else {
                $type = DScheduledTask::class;
            }
        }

        return $type;
    }

    /**
     * Determines if the task is currently initiating.
     *
     * @return    bool
     */
    public function isInitiating()
    {
        return $this->getFieldValue(self::FIELD_INITIATING);
    }

    /**
     * Determines if the task is currently pending.
     *
     * @return    bool
     */
    public function isPending()
    {
        return $this->getFieldValue(self::FIELD_PENDING);
    }

    /**
     * Determines if the task is currently running.
     *
     * @return    bool
     */
    public function isRunning()
    {
        return $this->getFieldValue(self::FIELD_RUNNING);
    }
}
