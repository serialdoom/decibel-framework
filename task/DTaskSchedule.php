<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

use app\decibel\application\DClassManager;
use app\decibel\authorise\DAuthorisationManager;
use app\decibel\authorise\DUser;
use app\decibel\configuration\DApplicationMode;
use app\decibel\debug\DProfiler;
use app\decibel\index\DIndexRecord;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DBooleanField;
use app\decibel\model\field\DDateTimeField;
use app\decibel\model\field\DFieldSearch;
use app\decibel\model\field\DIntegerField;
use app\decibel\model\field\DLinkedObjectField;
use app\decibel\model\field\DQualifiedNameField;
use app\decibel\model\index\DPrimaryIndex;
use app\decibel\process\DInvalidProcessIdException;
use app\decibel\process\DProcess;
use app\decibel\regional\DLabel;
use app\decibel\registry\DClassQuery;

/**
 * Maintains the execution schedule for {@link DScheduledTask} objects.
 *
 * @author    Timothy de Paris
 */
class DTaskSchedule extends DIndexRecord
{
    /** @var string 'Forced' field name. */
    const FIELD_FORCED = 'forced';

    /** @var string 'Owner' field name. */
    const FIELD_OWNER = 'owner';

    /** @var string 'Process ID' field name. */
    const FIELD_PROCESS_ID = 'processId';

    /** @var string 'Progress' field name. */
    const FIELD_PROGRESS = 'progress';

    /** @var string 'Scheduled' field name. */
    const FIELD_SCHEDULED = 'scheduled';

    /** @var string 'Started' field name. */
    const FIELD_STARTED = 'started';

    /** @var string 'Task' field name. */
    const FIELD_TASK = 'event';

    /**
     * Performs functionality to ensure no un-neccessary information is stored
     * in the database table for this index.
     *
     * @return    void
     */
    public static function cleanDatabase()
    {
        $user = DAuthorisationManager::getUser();
        // Cleanup invalid tasks.
        $validTasks = DClassManager::getClasses(DScheduledTask::class);
        self::search()
            ->filterByField(self::FIELD_TASK, $validTasks, DFieldSearch::OPERATOR_NOT_IN)
            ->delete($user);
        // Cleanup failed tasks.
        $runningTasks = self::search()
                            ->filterByField(self::FIELD_STARTED, null, DFieldSearch::OPERATOR_NOT_EQUAL)
                            ->getFields(array(
                                            self::FIELD_PROCESS_ID,
                                            self::FIELD_TASK,
                                        ));
        foreach ($runningTasks as $runningTask) {
            // Try to find the process for each event.
            try {
                DProcess::find($runningTask[ self::FIELD_PROCESS_ID ]);
                // If it doesn't exist, cancel the task.
            } catch (DInvalidProcessIdException $e) {
                /* @var $task DScheduledTask */
                $qualifiedName = $runningTask[ self::FIELD_TASK ];
                $task = new $qualifiedName();
                $task->cancel();
            }
        }
    }

    /**
     * Defines fields and indexes required by this index record.
     *
     * @return    void
     */
    public function define()
    {
        $labelUnknown = new DLabel('app\\decibel', 'unknown');
        $task = new DQualifiedNameField(self::FIELD_TASK, 'Task');
        $task->setAncestors(array(DScheduledTask::class));
        $task->setRequired(true);
        $this->addField($task);
        $scheduled = new DDateTimeField(self::FIELD_SCHEDULED, 'Scheduled Time');
        $scheduled->setRequired(true);
        $this->addField($scheduled);
        $owner = new DLinkedObjectField(self::FIELD_OWNER, 'Owner');
        $owner->setLinkTo(DUser::class);
        $owner->setNullOption($labelUnknown);
        $this->addField($owner);
        $forced = new DBooleanField(self::FIELD_FORCED, 'Forced');
        $forced->setDefault(false);
        $this->addField($forced);
        $started = new DDateTimeField(self::FIELD_STARTED, 'Started');
        $started->setNullOption('Not Started');
        $this->addField($started);
        $processID = new DIntegerField(self::FIELD_PROCESS_ID, 'Process ID');
        $processID->setNullOption($labelUnknown);
        $this->addField($processID);
        $progress = new DIntegerField(self::FIELD_PROGRESS, 'Progress');
        $progress->setNullOption('Not Started');
        $progress->setSize(2);
        $progress->setRequired(true);
        $this->addField($progress);
        $primaryIndex = new DPrimaryIndex();
        $primaryIndex->addField($task);
        $this->addIndex($primaryIndex);
    }

    /**
     * Returns the number of records that would be stored within the index
     * if all indexable models were included.
     *
     * @return    int
     */
    public static function getMaximumIndexSize()
    {
        $classes = DClassQuery::load()
                              ->setAncestor(DScheduledTask::class)
                              ->getClassNames();

        return count($classes);
    }

    /**
     * Returns the qualified name of the
     * {@link app::decibel::task::DScheduledTask DScheduledTask}
     * that can rebuild this index.
     *
     * @return    string
     */
    public static function getRebuildTaskName()
    {
        return null;
    }

    /**
     * Returns information about the currently scheduled tasks.
     *
     * @return    array    List of {@link DTaskStatus} objects.
     */
    public static function getSchedule()
    {
        $schedule = array();
        $tasks = self::search()
                     ->sortByField(self::FIELD_TASK)
                     ->getObjects();
        foreach ($tasks as $task) {
            try {
                $schedule[] = new DTaskStatus($task);
                // If the task status object throws an exception, there must
                // be an invalid task in the scheduler.
            } catch (DInvalidFieldValueException $exception) {
                self::cleanDatabase();
                self::updateUnscheduledTasks();
            }
        }

        return $schedule;
    }

    /**
     * Runs scheduled tasks that are to be run.
     *
     * @return    void
     */
    public static function runScheduledTasks()
    {
        // Ignore script execution time limit.
        set_time_limit(0);
        // Clean up any failed tasks and check that
        // all available tasks are scheduled.
        self::cleanDatabase();
        self::updateUnscheduledTasks();
        $tasks = self::search()
                     ->sortByField(self::FIELD_SCHEDULED)
                     ->filterByField(self::FIELD_STARTED, null)
                     ->filterByField(self::FIELD_SCHEDULED, time(), DFieldSearch::OPERATOR_LESS_THAN_OR_EQUAL)
                     ->getFields();
        foreach ($tasks as $task) {
            self::runScheduledTask($task);
        }
    }

    /**
     * Runs a scheduled task.
     *
     * @param    array $task Information about the task.
     *
     * @return    void
     */
    protected static function runScheduledTask(array $task)
    {
        /* @var $taskObject DTask */
        $taskQualifiedName = $task[ self::FIELD_TASK ];
        $taskObject = new $taskQualifiedName();
        if ($taskObject->canRunInMode()
            || $task[ self::FIELD_FORCED ]
        ) {
            DProfiler::startProfiling($taskQualifiedName);
            $output = $taskObject->run();
            if (DApplicationMode::isDebugMode()
                && $output
            ) {
                echo "Output of task '{$taskQualifiedName}':\n";
                echo "{$output}\n\n";
            }
            DProfiler::stopProfiling($taskQualifiedName);
        }
    }

    /**
     * Scheduled any regular tasks that are missing from the scheduler.
     *
     * @return    void
     */
    public static function updateUnscheduledTasks()
    {
        // Schedule unscheduled tasks.
        $scheduledTasks = self::search()
                              ->getField(self::FIELD_TASK);
        // Load a list of all available tasks.
        $availableTasks = array_merge(
            DClassManager::getClasses(DRegularTask::class),
            DClassManager::getClasses(DNightlyTask::class)
        );
        // Schedule any unscheduled tasks.
        $unscheduledTasks = array_diff($availableTasks, $scheduledTasks);
        foreach ($unscheduledTasks as $qualifiedName) {
            /* @var $task DScheduledTask */
            $task = new $qualifiedName();
            $task->finalise();
        }
    }
}
