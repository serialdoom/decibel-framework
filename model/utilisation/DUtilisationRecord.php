<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\utilisation;

use app\decibel\authorise\DAuthorisationManager;
use app\decibel\database\debug\DDatabaseException;
use app\decibel\database\DQuery;
use app\decibel\debug\DErrorHandler;
use app\decibel\index\DIndexRecord;
use app\decibel\model\DBaseModel;
use app\decibel\model\DModel;
use app\decibel\model\event\DModelEvent;
use app\decibel\model\field\DBooleanField;
use app\decibel\model\field\DField;
use app\decibel\model\field\DLinkedObjectField;
use app\decibel\model\field\DQualifiedNameField;
use app\decibel\model\index\DIndex;
use app\decibel\model\index\DPrimaryIndex;
use app\decibel\registry\DClassQuery;
use app\decibel\utility\DResult;

/**
 * Provides an index of feedable content on a website.
 *
 * @author        Timothy de Paris
 */
class DUtilisationRecord extends DIndexRecord
{
    /**
     * 'From' field name.
     *
     * @var        string
     */
    const FIELD_FROM = 'from';

    /**
     * 'Maintain Integrity' field name.
     *
     * @var        string
     */
    const FIELD_MAINTAIN_INTEGRITY = 'maintainIntegrity';

    /**
     * 'Qualified Name' field name.
     *
     * @var        string
     */
    const FIELD_QUALIFIED_NAME = 'qualifiedName';

    /**
     * 'To' field name.
     *
     * @var        string
     */
    const FIELD_TO = 'to';

    /**
     * Performs functionality to ensure no un-neccessary information is stored
     * in the database table for this index.
     *
     * @return    void
     */
    public static function cleanDatabase()
    {
        try {
            new DQuery('app\\decibel\\model\\utilisation\\DUtilisationRecord-clean');
        } catch (DDatabaseException $exception) {
            DErrorHandler::throwException($exception);
        }
    }

    /**
     * Defines fields and indexes required by this audit record.
     *
     * @return    void
     */
    public function define()
    {
        // Set field information.
        $qualifiedName = new DQualifiedNameField(self::FIELD_QUALIFIED_NAME, 'Linking Model Qualified Name');
        $qualifiedName->setAncestors(array(DBaseModel::class));
        $qualifiedName->setRequired(true);
        $this->addField($qualifiedName);
        $from = new DLinkedObjectField(self::FIELD_FROM, 'Linking Model Instance');
        $from->setLinkTo(DBaseModel::class);
        $from->setRequired(true);
        $this->addField($from);
        $to = new DLinkedObjectField(self::FIELD_TO, 'Linked Model Instance');
        $to->setLinkTo(DBaseModel::class);
        $to->setRequired(true);
        $this->addField($to);
        $maintainIntegrity = new DBooleanField(self::FIELD_MAINTAIN_INTEGRITY, 'Maintain Integrity');
        $maintainIntegrity->setRequired(true);
        $this->addField($maintainIntegrity);
        $primaryIndex = new DPrimaryIndex();
        $primaryIndex->addField($from);
        $primaryIndex->addField($to);
        $this->addIndex($primaryIndex);
        $toIndex = new DIndex('index_to');
        $toIndex->addField($to);
        $this->addIndex($toIndex);
    }

    /**
     * Removes all records from the index for a model instance.
     *
     * @param    DModelEvent $event An event triggered by the model instance.
     *
     * @return    void
     */
    public static function deIndex(DModelEvent $event)
    {
        $user = DAuthorisationManager::getUser();
        $instance = $event->getModelInstance();
        static::search()
              ->filterByField(self::FIELD_FROM, $instance->getId())
              ->delete($user);
    }

    /**
     * Returns the number of records currently stored within the index.
     *
     * This is not neccessarily the number of rows in the database table
     * for this index. It will usually return the number of distinct models
     * that have been included in the index.
     *
     * @return    int
     */
    public static function getCurrentIndexSize()
    {
        return static::search()
                     ->removeDefaultFilters()
                     ->groupBy(self::FIELD_FROM)
                     ->getCount();
    }

    /**
     * Returns the number of records that would be stored within the index
     * if all indexable models were included.
     *
     * @return    int
     */
    public static function getMaximumIndexSize()
    {
        return DModel::search()
                     ->removeDefaultFilters()
                     ->getCount();
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
        return 'app\\decibel\\model\\utilisation\\DRebuildUtilisationIndex';
    }

    /**
     * Returns a list containing the ID of each model instance that
     * holds a referential link to the specified model instance.
     *
     * @param    DModel $model
     * @param    bool   $ignoreReferences     If <code>true</code>, only links
     *                                        with referential integrity forced
     *                                        will be returned.
     *
     * @return    array
     */
    public static function getUtilisingIds(DModel $model, $ignoreReferences = false)
    {
        $qualifiedNames = DClassQuery::load()
                                     ->getClassNames();
        $search = self::search()
                      ->filterByField(self::FIELD_TO, $model->getId())
                      ->filterByField(self::FIELD_QUALIFIED_NAME, $qualifiedNames)
                      ->groupBy(self::FIELD_FROM);
        if ($ignoreReferences) {
            $search->filterByField(self::FIELD_MAINTAIN_INTEGRITY, true);
        }

        return $search->getField(self::FIELD_FROM);
    }

    /**
     * Includes a model instance in the index.
     *
     * @param    DModelEvent $event An event triggered by the model instance.
     *
     * @return    DResult
     */
    public static function index(DModelEvent $event)
    {
        $result = new DResult();
        // Clear any existing indexed information.
        DUtilisationRecord::deIndex($event);
        // Initialise the data array with information
        // that is common for all utilisation.
        $instance = $event->getModelInstance();
        $utilisationData = array(
            self::FIELD_FROM           => $instance->getId(),
            self::FIELD_QUALIFIED_NAME => get_class($instance),
        );
        foreach ($instance->getFields() as $field) {
            /* @var $field DField */
            $result->merge(DUtilisationRecord::indexField(
                $instance,
                $field,
                $utilisationData
            ));
        }
        // Ensure failure of indexing does not halt saving of a model.
        if (!$result->isSuccessful()) {
            $result->setSuccess(DResult::TYPE_WARNING, $result->getMessages());
        }

        return $result;
    }

    /**
     * Includes utilisation for the specified field
     * of a model instance in the index.
     *
     * @param    DModel $instance             Model instance to index.
     * @param    DField $field                Field to index.
     * @param    array  $utilisationData      Standard utilisation information
     *                                        for the model instance.
     *
     * @return    DResult
     */
    protected static function indexField(DModel $instance, DField $field,
                                         array $utilisationData)
    {
        $result = new DResult();
        $utilisation = $field->getUtilisation($instance);
        foreach ($utilisation as $linkedId => $relationalIntegrity) {
            $utilisationData[ self::FIELD_TO ] = $linkedId;
            $utilisationData[ self::FIELD_MAINTAIN_INTEGRITY ] = (bool)$relationalIntegrity;
            $result->merge(DUtilisationRecord::update($utilisationData));
        }

        return $result;
    }
}
