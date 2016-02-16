<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\model;

use app\decibel\auditing\DAuditRecord;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\model\event\DModelEvent;
use app\decibel\model\field\DEnumField;
use app\decibel\model\field\DFieldSearch;
use app\decibel\model\field\DIntegerField;
use app\decibel\model\field\DQualifiedNameField;
use app\decibel\model\field\DTextField;
use app\decibel\model\search\DBaseModelSearch;

/**
 * Provides auditing for actions performed on model instances.
 *
 * @author    Timothy de Paris
 */
class DModelActionRecord extends DAuditRecord
{
    /**
     * Denotes a user has created a model instance.
     *
     * @var        int
     */
    const ACTION_CREATED = 1;

    /**
     * Denotes a user has updated a model instance.
     *
     * @var        int
     */
    const ACTION_UPDATED = 2;

    /**
     * Denotes a user has deleted a model instance.
     *
     * @var        int
     */
    const ACTION_DELETED = 3;

    /**
     * 'Action' field name.
     *
     * @var        string
     */
    const FIELD_ACTION = 'action';

    /**
     * 'Comments' field name.
     *
     * @var        string
     */
    const FIELD_COMMENTS = 'comments';

    /**
     * 'Model' field name.
     *
     * @var        string
     */
    const FIELD_MODEL = 'model';

    /**
     * 'Qualified Name' field name.
     *
     * @var        string
     */
    const FIELD_QUALIFIED_NAME = 'qualifiedName';

    /**
     * 'String Value' field name.
     *
     * @var        string
     */
    const FIELD_STRING_VALUE = 'stringValue';

    /**
     * Available model actions.
     *
     * @var        array
     */
    protected static $actions = array(
        self::ACTION_CREATED => 'Created',
        self::ACTION_UPDATED => 'Updated',
        self::ACTION_DELETED => 'Deleted',
    );

    /**
     * Mapping of {@link DModelEvent} types to actions.
     *
     * @var        array
     */
    protected static $modelEvents = array(
        'app\\decibel\\model\\event\\DOnFirstSave'      => self::ACTION_CREATED,
        'app\\decibel\\model\\event\\DOnSubsequentSave' => self::ACTION_UPDATED,
        'app\\decibel\\model\\event\\DOnDelete'         => self::ACTION_DELETED,
    );

    /**
     * Defines fields and indexes required by this audit record.
     *
     * @return    void
     */
    protected function define()
    {
        $qualifiedName = new DQualifiedNameField(static::FIELD_QUALIFIED_NAME, 'Content Type');
        $qualifiedName->setAncestors(DBaseModel::class);
        $this->addField($qualifiedName);
        $model = new DIntegerField(static::FIELD_MODEL, 'Content ID');
        $model->setSize(8);
        $this->addField($model);
        $stringValue = new DTextField(static::FIELD_STRING_VALUE, 'Name');
        $stringValue->setMaxLength(255);
        $this->addField($stringValue);
        $action = new DEnumField(static::FIELD_ACTION, 'Action');
        $action->setValues(self::$actions);
        $this->addField($action);
        $comments = new DTextField(static::FIELD_COMMENTS, 'Comments');
        $comments->setNullOption('N/A');
        $comments->setMaxLength(1024);
        $comments->setDefault(null);
        $this->addField($comments);
    }

    /**
     * Returns a data array containing action records for the specified model.
     *
     * @param    DModel $model
     * @param    array  $types Required action types.
     *
     * @return    array
     */
    public static function getModelActionHistory(DModel $model, array $types = null)
    {
        $search = static::search();
        if ($types !== null) {
            $search->filterByField(static::FIELD_ACTION, $types, DFieldSearch::OPERATOR_IN);
        }
        $result = $search->filterByField(static::FIELD_MODEL, $model->getId())
                         ->sortByField(static::FIELD_CREATED, DBaseModelSearch::ORDER_DESC)
                         ->getFields();
        foreach ($result as &$record) {
            $record['actionName'] = static::$actions[ $record[ static::FIELD_ACTION ] ];
            $record[ static::FIELD_CREATED ] = date('d/m/Y H:i', $record[ static::FIELD_CREATED ]);
        }

        return $result;
    }

    /**
     * Logs an action occurring on a model.
     *
     * @param    DModelEvent $event
     *
     * @return    void
     */
    public static function logModelAction(DModelEvent $event)
    {
        $model = $event->getModelInstance();
        // Determine the action from the event type.
        $eventType = get_class($event);
        if (isset(static::$modelEvents[ $eventType ])) {
            $action = static::$modelEvents[ $eventType ];
        } else {
            throw new DInvalidParameterValueException(
                'event',
                array(get_called_class(), __FUNCTION__),
                'Supported app\\decibel\\model\\event\\DModelEvent instance'
            );
        }
        static::log(array(
                        static::FIELD_QUALIFIED_NAME => get_class($model),
                        static::FIELD_MODEL          => $model->getId(),
                        static::FIELD_STRING_VALUE   => (string)$model,
                        static::FIELD_ACTION         => $action,
                    ));
    }
}
