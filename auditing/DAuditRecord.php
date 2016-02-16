<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\auditing;

use app\decibel\auditing\DAuditSearch;
use app\decibel\authorise\DAuthorisationManager;
use app\decibel\authorise\DUser;
use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\database\DQuery;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\decorator\DDecoratable;
use app\decibel\decorator\DDecoratorCache;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\DPersistableDefinition;
use app\decibel\model\field\DEnumStringField;
use app\decibel\model\field\DIntegerField;
use app\decibel\model\field\DLinkedObjectField;
use app\decibel\model\index\DPrimaryIndex;
use app\decibel\regional\DLabel;
use app\decibel\utility\DResult;

/**
 * Defines the base class for auditing records.
 *
 * @author    Nikolay Dimitrov
 */
abstract class DAuditRecord extends DPersistableDefinition implements DDecoratable
{
    use DDecoratorCache;
    /**
     * 'Created By' field name.
     *
     * @var        string
     */
    const FIELD_CREATED_BY = 'createdBy';
    /**
     * 'Id' field name.
     *
     * @var        string
     */
    const FIELD_ID = 'id';
    /**
     * 'Retention Period' option name.
     *
     * @var        string
     */
    const OPTION_RETENTION_PERIOD = 'retentionPeriod';

    /**
     * 'One Week' retention period.
     *
     * @var        string
     */
    const RETENTION_ONE_WEEK = '-1 week';
    /**
     * 'One Month' retention period.
     *
     * @var        string
     */
    const RETENTION_ONE_MONTH = '-1 month';
    /**
     * 'Two Months' retention period.
     *
     * @var        string
     */
    const RETENTION_TWO_MONTHS = '-2 months';
    /**
     * 'Three Months' retention period.
     *
     * @var        string
     */
    const RETENTION_THREE_MONTHS = '-3 months';
    /**
     * 'Six Months' retention period.
     *
     * @var        string
     */
    const RETENTION_SIX_MONTHS = '-6 months';
    /**
     * 'One Year' retention period.
     *
     * @var        string
     */
    const RETENTION_ONE_YEAR = '-1 year';
    /**
     * 'Two Years' retention period.
     *
     * @var        string
     */
    const RETENTION_TWO_YEARS = '-2 years';
    /**
     * 'Five Years' retention period.
     *
     * @var        string
     */
    const RETENTION_FIVE_YEARS = '-5 years';
    /**
     * 'Ten Years' retention period.
     *
     * @var        string
     */
    const RETENTION_TEN_YEARS = '-10 years';

    /**
     * Implements any functionality required to remove extraneous records
     * from the database associated with this persistable object.
     *
     * Triggered by {@link app::decibel::database::maintenance::DOptimiseDatabase DOptimiseDatabase}.
     *
     * @return    DResult    The result of the operation, or <code>null</code>
     *                    if no action was performed.
     */
    public static function cleanDatabase()
    {
        return null;
    }

    /**
     * Log data in the audit log.
     *
     * @param    array $data Key/value data pairs.
     *
     * @return    DResult
     * @throws    DInvalidPropertyException    If no field exists with this name.
     * @throws    DInvalidFieldValueException    If an invalid value for the field
     *                                        is provided.
     */
    public static function log(array $data)
    {
        // Create a new audit record instance.
        $auditRecord = static::load(get_called_class());
        // Store the data against the audit record.
        foreach ($data as $fieldName => $value) {
            $auditRecord->setFieldValue($fieldName, $value);
        }
        $user = DAuthorisationManager::getUser();

        return $auditRecord->save($user);
    }

    /**
     * Constructs an instance of this audit record.
     *
     * @param    string $qualifiedName    Qualified name of the model this
     *                                    class defines (this may be itself).
     *
     * @return    DAuditRecord
     */
    final protected function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        // Add configurations to the definition.
        $retentionPeriod = new DEnumStringField(self::OPTION_RETENTION_PERIOD,
                                                new DLabel(self::class, self::OPTION_RETENTION_PERIOD));
        $retentionPeriod->setDescription(new DLabel(self::class, 'retentionPeriodDescription'));
        $retentionPeriod->setValues(static::getRetentionPeriods());
        $retentionPeriod->setMaxLength(9);
        $retentionPeriod->setRequired(true);
        $retentionPeriod->setDefault(self::RETENTION_ONE_MONTH);
        $this->addConfiguration($retentionPeriod);
        // Add fields to this definiton
        $id = new DIntegerField(self::FIELD_ID, new DLabel(self::class, self::FIELD_ID));
        $id->setUnsigned(true);
        $id->setAutoincrement(true);
        $id->setSize(8);
        $this->addField($id);
        $createdBy = new DLinkedObjectField(self::FIELD_CREATED_BY,
                                            new DLabel(self::class, self::FIELD_CREATED_BY));
        $createdBy->setLinkTo(DUser::class);
        $createdBy->setRelationalIntegrity(DLinkedObjectField::RELATIONAL_INTEGRITY_NONE);
        $createdBy->setReadOnly(true);
        $this->addField($createdBy);
        // Add primary key index.
        $index = new DPrimaryIndex();
        $index->addField($id);
        $this->addIndex($index);
        // Include custom fields for the extending record type.
        $this->define();
    }

    /**
     * Deletes the class instance from the database.
     *
     * @note
     * This method will always return an unsuccessful result, as audit records
     * are not able to be deleted.
     *
     * @param    DUser $user The user attempting to delete the model instance.
     *
     * @return    DResult
     */
    final public function delete(DUser $user)
    {
        return new DResult(
            static::getDisplayName(),
            new DLabel('app\\decibel', 'deleted'),
            false
        );
    }

    /**
     * Returns the unique ID for this model instance.
     *
     * @return    int
     */
    public function getId()
    {
        return $this->getFieldValue(self::FIELD_ID);
    }

    /**
     * Returns a list of available rentention periods for this audit record.
     *
     * @return    array
     */
    public static function getRetentionPeriods()
    {
        return array(
            self::RETENTION_ONE_WEEK     => '1 week',
            self::RETENTION_ONE_MONTH    => '1 month',
            self::RETENTION_TWO_MONTHS   => '2 months',
            self::RETENTION_THREE_MONTHS => '3 months',
            self::RETENTION_SIX_MONTHS   => '6 months',
            self::RETENTION_ONE_YEAR     => '1 year',
            self::RETENTION_TWO_YEARS    => '2 years',
            self::RETENTION_FIVE_YEARS   => '5 years',
            self::RETENTION_TEN_YEARS    => '10 years',
        );
    }

    /**
     * Builds the SQL query required to save an audit record of this type.
     *
     * @return    string
     */
    protected function getSaveSql()
    {
        $fieldNames = array_diff(
            array_keys($this->fields),
            array(self::FIELD_ID)
        );
        $sqlFields = array();
        foreach ($fieldNames as $fieldName) {
            $sqlFields[] = "`{$fieldName}`='#{$fieldName}#'";
        }

        return "INSERT INTO {$this->tableName} SET " . implode(', ', $sqlFields);
    }

    /**
     * Purges old records from this audit log, based on the configured
     * retention period.
     *
     * @return    bool
     */
    public function purge()
    {
        $time = strtotime($this->getOption(self::OPTION_RETENTION_PERIOD));
        try {
            $query = new DQuery('app\\decibel\\model\\DAuditRecord-purgeRecords', array(
                'table' => $this->tableName,
                'time'  => $time,
            ));
            $result = $query->isSuccessful();
        } catch (DQueryExecutionException $exception) {
            DErrorHandler::throwException($exception);
            $result = false;
        }

        return $result;
    }

    /**
     * Sets default values for this object before saving.
     *
     * @note
     * This function can be used to override previously set field values
     * if required.
     *
     * @return    void
     */
    protected function setDefaultValues()
    {
        $this->setFieldValue(self::FIELD_CREATED, time());
        $this->setFieldValue(self::FIELD_CREATED_BY, DAuthorisationManager::getResponsibleUser());
    }

    /**
     * Returns an DAuditSearch of the current type.
     *
     * @return    DAuditSearch
     */
    public static function search()
    {
        return new DAuditSearch(get_called_class());
    }
}
