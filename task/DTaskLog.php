<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
///@cond INTERNAL
namespace app\decibel\task;

use app\decibel\auditing\DAuditRecord;
use app\decibel\model\field\DEnumField;
use app\decibel\model\field\DQualifiedNameField;

/**
 * Defines an audit record for logging information about task execution.
 *
 * @author     Timothy de Paris
 * @ingroup    tasks_logs
 */
class DTaskLog extends DAuditRecord
{
    /** @var string 'Scheduled' task action. */
    const ACTION_SCHEDULED = 1;

    /** @var string 'Queued' task action. */
    const ACTION_QUEUED = 2;

    /** @var string 'Executed' task action. */
    const ACTION_EXECUTED = 3;

    /** @var string 'Cancelled' task action. */
    const ACTION_CANCELLED = 4;

    /** @var string 'Failed' task action. */
    const ACTION_FAILED = 5;

    /** @var string 'Re-scheduled' task action. */
    const ACTION_RESCHEDULED = 6;

    /**
     * 'Action' field name.
     *
     * @var string 'Action' field name.
     */
    const FIELD_ACTION = 'action';

    /**
     * 'Task' field name.
     *
     * @var string 'Task' field name.
     */
    const FIELD_TASK = 'task';

    /**
     * Returns a list of actions able to be performed on tasks.
     *
     * @return    array
     */
    public static function getActions()
    {
        return array(
            self::ACTION_SCHEDULED   => 'Scheduled',
            self::ACTION_RESCHEDULED => 'Re-scheduled',
            self::ACTION_QUEUED      => 'Queued',
            self::ACTION_EXECUTED    => 'Executed',
            self::ACTION_CANCELLED   => 'Cancelled',
            self::ACTION_FAILED      => 'Failed',
        );
    }

    /**
     * Defines fields and indexes required by this audit record.
     *
     * @return    void
     */
    protected function define()
    {
        $this->setDefaultConfigurationValue(
            self::OPTION_RETENTION_PERIOD,
            self::RETENTION_ONE_WEEK
        );
        $task = new DQualifiedNameField(self::FIELD_TASK, 'Task');
        $task->setAncestors(array(DTask::class));
        $this->addField($task);
        $action = new DEnumField('action', 'Action');
        $action->setValues(self::getActions());
        $this->addField($action);
    }
}
///@endcond
