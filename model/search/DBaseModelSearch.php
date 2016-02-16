<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

use app\decibel\cache\DModelSearchCache;
use app\decibel\database\DDatabase;
use app\decibel\database\debug\DDatabaseException;
use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\database\DQuery;
use app\decibel\database\statement\DJoin;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\decorator\DDecoratable;
use app\decibel\decorator\DDecoratorCache;
use app\decibel\file\DExportFormat;
use app\decibel\http\DOk;
use app\decibel\model\DBaseModel;
use app\decibel\model\DBaseModel_Definition;
use app\decibel\model\DChild;
use app\decibel\model\debug\DInvalidSearchException;
use app\decibel\model\debug\DSearchAlreadyExecutedException;
use app\decibel\model\debug\DUnknownModelInstanceException;
use app\decibel\model\DModel;
use app\decibel\model\field\DField;
use app\decibel\model\field\DLinkedObjectField;
use app\decibel\model\field\DRelationalField;
use app\decibel\model\index\DIndex;
use app\decibel\model\search\DFieldCondition;
use app\decibel\model\search\DFieldGroup;
use app\decibel\model\search\DFieldSearchExecuter;
use app\decibel\model\search\DFieldSelect;
use app\decibel\model\search\DFieldSort;
use app\decibel\model\search\DFieldsSearchExecuter;
use app\decibel\model\search\DGroupCriteria;
use app\decibel\model\search\DIdSearchExecuter;
use app\decibel\model\search\DIdsSearchExecuter;
use app\decibel\model\search\DIgnoreCondition;
use app\decibel\model\search\DLinkedFieldSort;
use app\decibel\model\search\DObjectSearchExecuter;
use app\decibel\model\search\DObjectsSearchExecuter;
use app\decibel\model\search\DOrCondition;
use app\decibel\model\search\DPaginatedFieldSearchExecuter;
use app\decibel\model\search\DPaginatedFieldsSearchExecuter;
use app\decibel\model\search\DPaginatedIdsSearchExecuter;
use app\decibel\model\search\DPaginatedObjectsSearchExecuter;
use app\decibel\model\search\DSearchCondition;
use app\decibel\model\search\DSelect;
use app\decibel\model\search\DSortCriteria;
use app\decibel\stream\DOutputStream;
use ArrayAccess;
use Countable;
use Iterator;

/**
 * Provides an abstracted interface to retrieve objects from a particular
 * model based on a set of criteria.
 *
 * @section       iterator_access Iterating a Model Search
 *
 * Keys will be ids (unless {@link DBaseModelSearch::useKey()} has been called)
 * and values will be model instances.
 *
 * Make sure you free the model instances ({@link DModel::free()} when iterating
 * over large datasets.
 *
 * @section       array_access Array Access
 *
 * Keys will be ids (unless {@link DBaseModelSearch::useKey()} has been called)
 * and values will be model instances.
 *
 * @author        Majid Afzal
 */
abstract class DBaseModelSearch implements ArrayAccess, Countable, Iterator, DDecoratable
{
    use DDecoratorCache;

    /**
     * Ascending sort order.
     *
     * @var        string
     */
    const ORDER_ASC = 'ASC';

    /**
     * Descending sort order.
     *
     * @var        string
     */
    const ORDER_DESC = 'DESC';

    /**
     * SQL maximum aggregate function.
     *
     * @var        string
     */
    const AGGREGATE_MAX = 'MAX';

    /**
     * SQL minimum aggregate function.
     *
     * @var        string
     */
    const AGGREGATE_MIN = 'MIN';

    /**
     * SQL average aggregate function.
     *
     * @var        string
     */
    const AGGREGATE_AVG = 'AVG';

    /**
     * SQL sum aggregate function.
     *
     * @var        string
     */
    const AGGREGATE_SUM = 'SUM';

    /**
     * SQL count aggregate function.
     *
     * @var        string
     */
    const AGGREGATE_COUNT = 'COUNT';

    /**
     * SQL group concat aggregate function.
     *
     * @var        string
     */
    const AGGREGATE_GROUP_CONCAT = 'GROUP_CONCAT';

    /**
     * Placeholder to indicate no aggregate function is to be used.
     *
     * @var        string
     */
    const AGGREGATE_NONE = 'NONE';

    /**
     * Return type used to specify that field values should be returned
     * in unserialized form.
     *
     * This is the database data type form of a field value (e.g. interger,
     * string or array) and is returned by the {@link DField::serialize()}
     * method appropriate to the field.
     *
     * Exmaples of serialized field values include:
     * - The integer ID for a {@link app::decibel::model::field::DLinkedObjectField DLinkedObjectField}
     * - An array of integer IDs for a {@link app::decibel::model::field::DLinkedObjectsField DLinkedObjectsField}
     *
     * This constant can be provided to the <code>$returnType</code> parameter
     * of the {@link DBaseModelSearch::getField()} and
     * {@link DBaseModelSearch::getFields()} methods.
     *
     * @var        string
     */
    const RETURN_UNSERIALIZED = 'unserialized';

    /**
     * Return type used to specify that field values should be returned
     * in serialized form.
     *
     * This is the PHP data type form of a field value (e.g. object instance)
     * and is returned by the {@link DField::unserialize()} method appropriate
     * to the field.
     *
     * Exmaples of serialized field values include:
     * - A {@link DBaseModel} instance for a {@link app::decibel::model::field::DLinkedObjectField DLinkedObjectField}
     * - An array of {@link DBaseModel} instances for a {@link app::decibel::model::field::DLinkedObjectsField
     * DLinkedObjectsField}
     *
     * This constant can be provided to the <code>$returnType</code> parameter
     * of the {@link DBaseModelSearch::getField()} and
     * {@link DBaseModelSearch::getFields()} methods.
     *
     * @var        string
     */
    const RETURN_SERIALIZED = 'serialized';

    /**
     * Return type used to specify that field values should be returned
     * in string form.
     *
     * This is the human-readable form of a field value* and is returned
     * by the {@link DField::toString()} method appropriate to the field.
     *
     * This constant can be provided to the <code>$returnType</code> parameter
     * of the {@link DBaseModelSearch::getField()} and
     * {@link DBaseModelSearch::getFields()} methods.
     *
     * @var        string
     */
    const RETURN_STRING_VALUES = 'stringValues';

    /**
     * Available orders for searches.
     *
     * @var        array
     */
    protected static $orders = array(
        self::ORDER_ASC  => 'Ascending',
        self::ORDER_DESC => 'Descending',
    );

    /**
     * Qualified name of the object being search.
     *
     * @var        string
     */
    protected $qualifiedName;

    /**
     * Name of the database table for the object being searched.
     *
     * @var        string
     */
    protected $tableName;

    /**
     * Whether the results of this search will be cached.
     *
     * @var        bool
     */
    protected $cacheResults = false;

    /**
     * Whether the DBaseModelSearch will execute in debug mode.
     *
     * @var        bool
     */
    protected $debug = false;

    /**
     * The fields to include in search results.
     *
     * @var        array    List of {@link DSelect} objects
     */
    protected $fields = array();

    /**
     * Specified the maximum number of results that will be returned by the search.
     *
     * @var        int
     */
    protected $limitTo;

    /**
     * Specified starting row that will be returned by the search.
     *
     * @var        int
     */
    protected $limitFrom = 0;

    /**
     * Search conditions for this search.
     *
     * @var        array
     */
    protected $conditions = array();

    /**
     * Joins that the object must construct to return the results
     *
     * @var        array
     */
    protected $join = array();

    /**
     * Joins that were added directly by thje user.
     *
     * Pointers to these joins are added here before the search is prepared,
     * as any joins added programatically during preparation must be removed
     * if the search is cloned.
     *
     * @var        array
     */
    protected $userJoins = array();

    /**
     * Fields by which the search results will be grouped.
     *
     * @var        array
     */
    protected $group = array();

    /**
     * Fields by which the search results will be sorted.
     *
     * @var        array
     */
    protected $sort = array();

    /**
     * Corresponding sort order for each of the fields by which the search
     * results will be sorted.
     *
     * @var        array
     */
    protected $sortOrder = array();

    /**
     * GROUP BY clauses to be used when building the SQL query.
     *
     * @var        array
     */
    protected $groupSql = array();

    /**
     * JOIN clauses to be used when building the SQL query.
     *
     * @var        array
     */
    protected $joinSql = array();

    /**
     * SELECT clauses to be used when building the SQL query.
     *
     * @var        array
     */
    protected $selectSql = array();

    /**
     * SORT BY clauses to be used when building the SQL query.
     *
     * @var        array
     */
    protected $sortSql = array();

    /**
     * WHERE clauses to be used when building the SQL query.
     *
     * @var        array
     */
    protected $whereSql = array();

    /**
     * HAVING clauses to be used when building the SQL query.
     *
     * @var        array
     */
    protected $havingSql = array();

    /**
     * The SQL executed by the search.
     *
     * Used for debugging purposes only via {@link DBaseModelSearch::getExecutedSql()}
     *
     * @var        string
     */
    protected $sql;

    /**
     * Whether the model search has been prepared.
     *
     * @var        bool
     */
    protected $prepared = false;

    /**
     * Name of the field to use as a key for {@link DBaseModelSearch::getObjects}
     * or when using the model search as an iterator or array.
     *
     * @var        string
     */
    protected $key = false;

    /**
     * Used to keep track of field conditions numbers
     *
     * @var        int
     */
    private $keyCounter = 0;

    /**
     * The internal iterator position.
     *
     * @var        int
     */
    private $position;

    /**
     * Stores the next model to be retrieved when this search is being
     * used as an iterator.
     *
     * @var        DBaseModel
     */
    private $currentModel;

    /**
     * IDs for matching objects stored when the DModel is used as an iterator.
     *
     * @var        array
     */
    protected $ids;

    /**
     * Keys for matching objects stored when the DModel is used as an iterator.
     *
     * This is only populated where the key is not the ID field.
     *
     * @var        array
     */
    protected $keys;

    /**
     * List of IDs of objects validated to have the correct permissions
     * for the current user.
     *
     * @var        array
     */
    protected $validObjects;

    /**
     * The database on which this search will be performed.
     *
     * @var        DDatabase
     */
    protected $database = null;

    /**
     * Creates a new DBaseModelSearch.
     *
     * @param    string $qualifiedName        Qualified name of the model that will
     *                                        be searched.
     *
     * @return    static
     */
    abstract public function __construct($qualifiedName);

    /**
     * Sets the database to execute the search on.
     *
     * @param    DDatabase $database Database to search on
     *
     * @return    static
     */
    public function setDatabase(DDatabase $database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * Updates the model search following cloning.
     *
     * @return    void
     */
    public function __clone()
    {
        $this->groupSql = array();
        $this->havingSql = array();
        $this->joinSql = array();
        $this->selectSql = array();
        $this->sortSql = array();
        $this->whereSql = array();
        $this->prepared = false;
        $this->join = $this->userJoins;
    }

    ///@cond INTERNAL
    /**
     * Handles retrieval of parameters.
     *
     * @param    string $name The name of the parameter to retrieve.
     *
     * @return    mixed
     */
    public function __get($name)
    {
        if ($name === 'fields') {
            $value = array_keys($this->fields);
        } else {
            if (property_exists($this, $name)) {
                $value = $this->$name;
            } else {
                throw new DInvalidPropertyException($name);
            }
        }

        return $value;
    }
    ///@endcond
    /**
     * Returns an array of fields that will be included in a serialized
     * version of this object.
     *
     * @return    array
     */
    public function __sleep()
    {
        // Return a list of fields that need to be included in the cache
        // ID for this search to ensure there are no cache conflicts.
        // "fields" does not need to be included here as individual
        // select cache IDs are added to the search cache ID by the
        // {@link DBaseModelSearch::getCacheId()} method.
        return array(
            'qualifiedName',
            'join',
            'key',
            'limitTo',
            'limitFrom',
            'conditions',
            'group',
            'sort',
            'sortOrder',
        );
    }

    /**
     * Add condition to the search.
     *
     * @param    DSearchCondition $condition
     *
     * @return    static
     * @throws    DSearchAlreadyExecutedException    If a condition is added to a
     *                                            model search that has already
     *                                            been executed.
     */
    public function addCondition(DSearchCondition $condition)
    {
        if ($this->prepared) {
            throw new DSearchAlreadyExecutedException();
        }
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * Adds a join to this search.
     *
     * @param    DJoin $join The join to add.
     *
     * @return    static
     */
    public function addJoin(DJoin $join)
    {
        $alias = trim($join->getAlias(), '`');
        $tableNameTrimmed = trim($this->tableName, '`');
        if (!isset($this->join[ $alias ])
            && $alias !== $tableNameTrimmed
        ) {
            $this->join[ $alias ] = $join;
        }

        return $this;
    }

    /**
     * Add joins to this search.
     *
     * @param    array $joins
     *
     * @return    static
     */
    public function addJoins(array $joins)
    {
        foreach ($joins as $join) {
            if ($join) {
                $this->addJoin($join);
            }
        }

        return $this;
    }

    /**
     * Applies a sort criteria to the search.
     *
     * @param    DSortCriteria $criteria      The sort criteria.
     * @param    string        $order         Sort order. One of:
     *                                        - {@link DBaseModelSearch::ORDER_ASC}
     *                                        - {@link DBaseModelSearch::ORDER_DESC}
     * @param    bool          $append        If <code>true</code>, the new criteria
     *                                        will be appended to any existing sort
     *                                        criteria, otherwise the new critera
     *                                        will replace any existing criteria.
     *
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     * @return    static
     */
    public function addSortCriteria(DSortCriteria $criteria,
                                    $order = DBaseModelSearch::ORDER_ASC, $append = true)
    {
        // Check valid sort order.
        if (!isset(static::$orders[ $order ])) {
            throw new DInvalidParameterValueException(
                'order',
                array(__CLASS__, __FUNCTION),
                '<code>DBaseModelSearch::ORDER_ASC</code> or <code>DBaseModelSearch::ORDER_DESC</code>'
            );
        }
        if ($append) {
            // If this field is already listed, remove it first.
            $position = array_search($criteria, $this->sort);
            if ($position !== false) {
                unset($this->sort[ $position ]);
            }
            $this->sort[] = $criteria;
            $this->sortOrder[] = $order;
        } else {
            $this->sort = array($criteria);
            $this->sortOrder = array($order);
        }

        return $this;
    }

    ///@cond INTERNAL
    /**
     * Applies any required default grouping for this search, if no group
     * criteria have been supplied.
     *
     * @return    void
     */
    protected function applyDefaultGroup()
    {
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Prepares an array of SQL select statements for this search.
     *
     * @param    array  $fields       List of {@link DFieldSelect} objects. If this
     *                                is an empty array, each field from the model's
     *                                definition will be selected.
     * @param    string $returnType   How field values will be returned if not
     *                                specified by the {@link DFieldSelect} object.
     *                                One of:
     *                                - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                                - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                                - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     *
     * @return    array    List of SQL select statements.
     */
    protected function buildSelectSql(array &$fields, $returnType)
    {
        // Add all fields for this model if none requested.
        if (!$fields) {
            $fields = array();
            $definitionFields = $this->definition->getFields();
            foreach ($definitionFields as $fieldName => $field) {
                $fields[ $fieldName ] = new DFieldSelect($fieldName);
            }
        }
        // Create select fields and add required joins.
        $selectSql = array();
        foreach ($fields as $select) {
            $selectSqlPart = $select->getSelect($this, $returnType);
            if ($selectSqlPart !== null) {
                $selectSql[] = $selectSqlPart;
            }
        }

        return $selectSql;
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Builds an SQL query from the component parts prepared
     * by {@link DBaseModelSearch::prepare()}.
     *
     * @note
     * The {@link DBaseModelSearch::prepare()} method must be called
     * before using this method to ensure all component parts are prepared.
     *
     * @param    array $selectSql     List of fields to be selected.
     * @param    bool  $distinct      Whether the DISTINCT operator should
     *                                be added to the SQL.
     * @param    bool  $applyGroup    Whether to add a GROUP BY clause.
     * @param    bool  $applySort     Whether to add a SORT clause.
     * @param    bool  $applyLimit    Whether to add a LIMIT clause.
     *
     * @return    string
     */
    protected function buildSql(array $selectSql, $distinct = true,
                                $applyGroup = true, $applySort = true, $applyLimit = true)
    {
        // Build query SQL.
        $sql = "SELECT " . ($distinct ? 'DISTINCT ' : '')
            . implode(', ', $selectSql)
            . " FROM `{$this->tableName}`";
        // Add joins.
        if (count($this->joinSql)) {
            $sql .= implode(' ', $this->joinSql);
        }
        // Add where conditions.
        if (count($this->whereSql)) {
            $sql .= " WHERE " . implode(" AND ", $this->whereSql);
        }
        // Set group by.
        if ($applyGroup && count($this->groupSql)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupSql);
            // Add having conditions.
            // Only applicable if there is a group.
            if (count($this->havingSql)) {
                $sql .= " HAVING " . implode(" AND ", $this->havingSql);
            }
        }
        // Set sort order.
        if ($applySort && count($this->sortSql)) {
            $sql .= " ORDER BY " . implode(', ', $this->sortSql);
        }
        // Limit number of results.
        if ($applyLimit && $this->limitTo) {
            $sql .= " LIMIT {$this->limitFrom}, {$this->limitTo}";
        }

        return $sql;
    }
    ///@endcond
    /**
     * Causes the DBaseModelSearch to execute in debug mode.
     *
     * When in debug mode, the cache is not checked (therefore always executing
     * the search query) and the executed query SQL will be debugged.
     *
     * @return    static
     */
    public function debug()
    {
        $this->debug = true;
        $this->cacheResults = false;

        return $this;
    }

    /**
     * Allows caching of results for this search to be enabled or disabled.
     *
     * Caching of results may be disabled when many different searches are
     * performed (for example taxonomised filters) and cache space is limited,
     * to avoid eviction of more important data.
     *
     * @param    bool $enabled Whether caching will be enabled.
     *
     * @return    static
     */
    public function enableCaching($enabled = true)
    {
        $this->cacheResults = ($enabled && !$this->debug);

        return $this;
    }

    /**
     * Removes any default filters from the serach.
     *
     * @return    static
     */
    public function removeDefaultFilters()
    {
        return $this;
    }

    /**
     * Filters the search results based on the value of a field.
     *
     * This method is a shortcut for the following code:
     *
     * @code
     * ->addCondition(new DFieldCondition($field, $value, $operator))
     * @endcode
     *
     * It is also possible to filter on the field of a model instance linked
     * to this model instance by providing a chain of field names as an array
     * to the <code>$field</code> parameter, for example:
     *
     * @code
     * ->filterByField(array('user', 'username'), 'root');
     * @endcode
     *
     * This will return all searched objects that are linked to a user
     * with the username 'root'.
     *
     * @note
     * When used with {@link app::decibel::model::field::DLinkedObjectsField DLinkedObjectsField},
     * <code>null</code> can be provided as a value to return model instances
     * that have the field not linking to any other object.
     *
     * @param    string $fieldName    Name of the field or index to search on.
     * @param    mixed  $value        Search value.
     * @param    string $operator     The operator use with the object. If not provided
     *                                the default operator for the field type will be used.
     *
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     * @return    DBaseModelSearch
     */
    public function filterByField($fieldName, $value, $operator = null)
    {
        return $this->addCondition(new DFieldCondition($fieldName, $value, $operator));
    }

    /**
     * Filters the search results based on the value of an index.
     *
     * @param    string $indexName    Name of the field or index to search on.
     * @param    mixed  $value        Search value.
     * @param    string $operator     The operator use with the object. If not provided
     *                                the default operator for the field type will be used.
     *
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     * @return    DBaseModelSearch
     */
    public function filterByIndex($indexName, $value, $operator = null)
    {
        $definition = $this->getDefinition();
        $indexes = $definition->getIndexes();
        if (!isset($indexes[ $indexName ])) {
            throw new DInvalidParameterValueException(
                'indexName',
                array(__CLASS__, __FUNCTION__),
                "provided index <code>{$indexName}</code> is not a valid index in the model <code>{$definition->qualifiedName}</code>"
            );
        }
        // Load the index from the definition.
        /* @var $index DIndex */
        $index = $indexes[ $indexName ];
        $fields = $index->getFields();
        // If there is more than one field, create an OR condition.
        if (count($fields) > 1) {
            $condition = new DOrCondition();
            foreach ($fields as $field) {
                /* @var $field DField */
                $condition->addCondition(new DFieldCondition(
                                             $field->getName(),
                                             $value,
                                             $operator
                                         ));
            }
            // Otherwise, add a single condition.
        } else {
            $field = array_pop($fields);
            $condition = new DFieldCondition(
                $field->getName(),
                $value,
                $operator
            );
        }

        return $this->addCondition($condition);
    }

    /**
     * Determines how long results of this search can be cached for.
     *
     * @param    mixed $validObjects      List of IDs of objects to be included
     *                                    in this search, or <code>true</code>
     *                                    if all objects are valid.
     *
     * @return    int        The timestamp until which results can be cached,
     *                    or <code>null</code> if no expiry is required.
     */
    protected function getCacheExpiry($validObjects)
    {
        return null;
    }

    /**
     * Returns information about the fields included in this search.
     *
     * @return    array    List of {@link app::decibel::model::search::DSelect DSelect}
     *                    objects.
     */
    public function getIncludedFields()
    {
        return $this->fields;
    }

    /**
     * Ignores specified objects from the search regardless
     * of any other search options.
     *
     * @param    array $ignore Objects or IDs to ignore.
     *
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     * @return    DBaseModelSearch
     */
    public function ignore(array $ignore)
    {
        return $this->addCondition(new DIgnoreCondition($ignore));
    }

    /**
     * Set the the maximum number of results to return.
     *
     * By default, searches will not be limited.
     *
     * @param    int $limitTo         Maximum number of results this search should
     *                                return, or <code>null</code> to remove any
     *                                existing limit.
     * @param    int $limitFrom       Number of results this search should return.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     */
    public function limitTo($limitTo, $limitFrom = 0)
    {
        if (!is_numeric($limitTo)
            && $limitTo !== null
        ) {
            throw new DInvalidParameterValueException(
                'limitTo',
                array(__CLASS__, __FUNCTION__),
                'integer or null'
            );
        }
        $this->limitTo = $limitTo;
        $this->limitFrom = $limitFrom;

        return $this;
    }

    /**
     * Provides a criteria by which to group the results of the search.
     *
     * @param    DGroupCriteria $criteria Criteria by which to group.
     * @param    bool           $append   If <code>true</code>, the new criteria will
     *                                    be appended    to any existing group by criteria.
     *                                    If <code>false</code>, the new critera will
     *                                    replace any existing criteria.
     *
     * @throws    DInvalidParameterValueException    If the provided field cannot
     *                                            be used to group the result
     *                                            of a model search.
     * @return    static
     */
    public function groupBy($criteria, $append = true)
    {
        // Backwards compatibility for provision of a field name.
        if (is_string($criteria)) {
            // Validate the field name.
            $definitionFields = $this->definition->getFields();
            if (!isset($definitionFields[ $criteria ])) {
                throw new DInvalidParamterValueException(
                    'groupBy',
                    array(__CLASS__, __FUNCTION__),
                    'app\\decibel\\model\\search\DGroupCriteria instance'
                );
            }
            // Always include any grouped fields in the results so they make sense.
            // The join will be added in DBaseModelSearch::prepare() so we don't
            // need to worry about that.
            $this->includeField($criteria);
            // Create a group criteria for the field.
            $criteria = new DFieldGroup($definitionFields[ $criteria ]);
        }
        if ($append) {
            $this->group[] = $criteria;
        } else {
            $this->group = array($criteria);
        }

        return $this;
    }

    /**
     * Determines if a filter has been applied to this search
     * for the specified field.
     *
     * @param    string $fieldName Name of the field to check.
     *
     * @return    bool
     */
    public function isFilteredByField($fieldName)
    {
        $isFiltered = false;
        foreach ($this->conditions as $condition) {
            /* @var $condition DSearchCondition */
            if ($condition instanceof DFieldCondition
                && $condition->includesField($fieldName)
            ) {
                $isFiltered = true;
                break;
            }
        }

        return $isFiltered;
    }

    ///@cond INTERNAL
    /**
     * Set name of the Object's Fields OR Taxonomy Object that will be used
     * to sort results.
     *
     * @param    mixed  $criteria     Name of the field to sort by
     *                                or a DSortCriteria instance.
     * @param    string $order        Sort order. One of {@link DBaseModelSearch::ORDER_ASC}
     *                                or {@link DBaseModelSearch::ORDER_DESC}.
     * @param    bool   $append       If true, the new criteria will be appended
     *                                to any existing sort criteria. If false,
     *                                the new critera will replace any existing
     *                                sort criteria. Defaults to true.
     *
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     * @return    static
     * @deprecated    In favour of {@link DBaseModelSearch::addSortCriteria()},
     *                {@link DBaseModelSearch::sortByField()}
     *                or {@link DBaseModelSearch::sortByLinkedField()}
     */
    public function sortBy($criteria, $order = DBaseModelSearch::ORDER_ASC,
                           $append = true)
    {
        // Pass field names through to sortByField.
        if (is_string($criteria)) {
            $this->sortByField($criteria, $order, $append);
            // Not a valid sort criteria.
        } else {
            if (!$criteria instanceof DSortCriteria) {
                throw new DInvalidParameterValueException(
                    'criteria',
                    array(__CLASS__, __FUNCTION__),
                    'app\\decibel\\model\\search\DSortCriteria instance'
                );
                // Apply the sort criteria.
            } else {
                $this->addSortCriteria($criteria, $order, $append);
            }
        }

        return $this;
    }
    ///@endcond
    /**
     * Applies a linked field sort criteria to the search.
     *
     * This method is a shortcut for the following code:
     *
     * <code>
     * ->addSortCriteria(new DLinkedFieldSort($linkField, $field));
     * </code>
     *
     * @param    string $fieldName    Name of the field to sort on.
     * @param    string $order        Sort order. One of:
     *                                - {@link DBaseModelSearch::ORDER_ASC}
     *                                - {@link DBaseModelSearch::ORDER_DESC}
     * @param    bool   $append       If <code>true</code>, the new criteria
     *                                will be appended to any existing sort
     *                                criteria, otherwise the new critera
     *                                will replace any existing criteria.
     *
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     * @return    DBaseModelSearch
     */
    public function sortByField($fieldName,
                                $order = DBaseModelSearch::ORDER_ASC,
                                $append = true)
    {
        $definitionFields = $this->definition->getFields();
        if (!isset($definitionFields[ $fieldName ])) {
            throw new DInvalidParameterValueException(
                'fieldName',
                array(__CLASS__, __FUNCTION__),
                'Name of a valid field'
            );
        }
        $criteria = new DFieldSort($definitionFields[ $fieldName ]);

        // Apply the sort criteria.
        return $this->addSortCriteria($criteria, $order, $append);
    }

    /**
     * Applies a linked field sort criteria to the search.
     *
     * This method is a shortcut for the following code:
     *
     * <code>
     * ->addSortCriteria(new DLinkedFieldSort($linkField, $field));
     * </code>
     *
     * @param    string $linkField    Name of the linking field in the searched model.
     * @param    string $field        Name of the field to sort on in the linked model.
     * @param    string $order        Sort order. One of:
     *                                - {@link DBaseModelSearch::ORDER_ASC}
     *                                - {@link DBaseModelSearch::ORDER_DESC}
     * @param    bool   $append       If <code>true</code>, the new criteria
     *                                will be appended to any existing sort
     *                                criteria, otherwise the new critera
     *                                will replace any existing criteria.
     *
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     * @return    DBaseModelSearch
     */
    public function sortByLinkedField($linkField, $field,
                                      $order = DBaseModelSearch::ORDER_ASC,
                                      $append = true)
    {
        $definitionFields = $this->definition->getFields();
        if (!isset($definitionFields[ $linkField ])
            || !$definitionFields[ $linkField ] instanceof DLinkedObjectField
        ) {
            throw new DInvalidParameterValueException(
                'linkField',
                array(__CLASS__, __FUNCTION__),
                'Name of a valid DLinkedObjectField'
            );
        }
        $linkTo = $definitionFields[ $linkField ]->linkTo;
        $linkDefinitionName = $linkTo::getDefinition();
        $linkDefinition = new $linkDefinitionName($linkTo);
        $linkDefinitionFields = $linkDefinition->getFields();
        if (!isset($linkDefinitionFields[ $field ])) {
            throw new DInvalidParameterValueException(
                'linkField',
                array(__CLASS__, __FUNCTION__),
                'Name of a valid field'
            );
        }
        $criteria = new DLinkedFieldSort(
            $definitionFields[ $linkField ],
            $linkDefinitionFields[ $field ]
        );

        // Apply the sort criteria.
        return $this->addSortCriteria($criteria, $order, $append);
    }

    /**
     * Includes a select that will be returned when executing the search using
     * the {@link DBaseModelSearch::getFields()} method.
     *
     * @param    DSelect $select The select definition.
     *
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     * @return    static
     */
    public function includeSelect(DSelect $select)
    {
        // Determine name of field in results and check this is unique.
        $fieldName = $select->getFieldName();
        if (isset($this->fields[ $fieldName ])) {
            throw new DInvalidParameterValueException(
                'select',
                array(__CLASS__, __FUNCTION__),
                'unique select alias'
            );
        }
        // Register field with the search.
        $this->fields[ $fieldName ] = $select;

        return $this;
    }

    /**
     * Includes an aggregated field that will be returned when executing
     * the search using the {@link DBaseModelSearch::getFields()} method.
     *
     * The following example will return the last expiry date for all articles
     * that have one or more comments:
     *
     * @code
     * Article::search()
     *    ->filterByField('commentCount', 0, '>')
     *    ->includeAggregateField('expiryDate', DBaseModelSearch::AGGREGATE_MAX)
     *    ->getFields();
     * @endcode
     *
     * The return value will be:
     *
     * @code
     * array(
     *    'expiryDate'    => 1342544312
     * );
     * @endcode
     *
     * @param    string $fieldName    The field to include in returned results.
     *                                Could be an array with a chain of linked fields
     *                                where the last element is the name of the actual
     *                                field to select.
     * @param    string $function     The aggregate function to apply to the field.
     *                                One of:
     *                                - {@link DBaseModelSearch::AGGREGATE_NONE}
     *                                - {@link DBaseModelSearch::AGGREGATE_MAX}
     *                                - {@link DBaseModelSearch::AGGREGATE_MIN}
     *                                - {@link DBaseModelSearch::AGGREGATE_AVG}
     *                                - {@link DBaseModelSearch::AGGREGATE_SUM}
     *                                - {@link DBaseModelSearch::AGGREGATE_COUNT}
     * @param    string $returnType   How field values will be returned. One of:
     *                                - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                                - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                                - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     *                                If not provided, the value of the $returnType
     *                                parameter passed to the result retrieval
     *                                function ({@link DBaseModelSearch::getField()}
     *                                or {@link DBaseModelSearch::getFields()}
     *                                will be used.
     * @param    string $alias        Name of the field in returned results.
     *                                If not provided, the field name will be used.
     *
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     * @return    static
     * @todo    Throw exception for non-unique fieldName rather than handling.
     */
    public function includeAggregateField($fieldName, $function,
                                          $returnType = null, $alias = null)
    {
        // Create select object to represent this field.
        $select = new DFieldSelect(
            $fieldName,
            $function,
            $returnType,
            $alias
        );
        // Determine name of field in results and check this is unique.
        $fieldName = $select->getFieldName();
        if (isset($this->fields[ $fieldName ])) {
            if ($alias !== null) {
                throw new DInvalidParameterValueException(
                    'alias',
                    array(__CLASS__, __FUNCTION__),
                    'unique field alias'
                );
            }
            if ($fieldName !== 'id') {
                throw new DInvalidParameterValueException(
                    'fieldName',
                    array(__CLASS__, __FUNCTION__),
                    'unique field name'
                );
            }
        }
        // Register field with the search.
        $this->fields[ $fieldName ] = $select;

        return $this;
    }

    /**
     * Includes a field that will be returned when executing the search using
     * the {@link DBaseModelSearch::getFields()} method.
     *
     * @param    string $fieldName    The field to return in the search result.
     * @param    string $returnType   How field values will be returned. One of:
     *                                - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                                - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                                - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     *                                If not provided, the value of the $returnType
     *                                parameter passed to the result retrieval
     *                                function ({@link DBaseModelSearch::getField()}
     *                                or {@link DBaseModelSearch::getFields()}
     *                                will be used.
     * @param    string $alias        Name of the field in returned results.
     *                                If not provided, the field name will be used.
     *
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     * @return    static
     */
    public function includeField($fieldName, $returnType = null, $alias = null)
    {
        try {
            $this->includeAggregateField(
                $fieldName,
                DBaseModelSearch::AGGREGATE_NONE,
                $returnType,
                $alias
            );
        } catch (DInvalidParameterValueException $e) {
            $e->regenerateBacktrace();
            throw $e;
        }

        return $this;
    }

    /**
     * Includes fields that will be returned when executing the search using
     * the {@link DBaseModelSearch::getFields()} method.
     *
     * This function can be passed an array of field names, or each field
     * name as an separate parameter, for example:
     *
     * @code
     * // Array of field names.
     * $search->includeFields(array('field1', 'field2'));
     *
     * // Individual field names.
     * $search->includeFields('field1', 'field2');
     * @endcode
     *
     * @note
     * All values for fields included by this method will be returned based
     * on the value of the $returnType parameter passed to the result retrieval
     * function ({@link DBaseModelSearch::getField()} or {@link DBaseModelSearch::getFields()}.
     * Use {@link DBaseModelSearch::includeField()} if different return types
     * are required for different fields.
     *
     * @param    array $fieldNames The list of fields to include in the search result.
     *
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     * @return    static
     */
    public function includeFields($fieldNames)
    {
        // Normalise parameters.
        if (!is_array($fieldNames)) {
            $fieldNames = func_get_args();
        }
        foreach ($fieldNames as $field) {
            try {
                $this->includeField($field);
            } catch (DInvalidParameterValueException $e) {
                $e->regenerateBacktrace();
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Sets the key that will be used by the model search.
     *
     * The key is used when returning associative arrays or results from
     * the {@link DBaseModelSearch::getObjects()} method and when the model
     * search is used as an iterator or array.
     *
     * If not called, the model's ID field will be used.
     *
     * @param    string $fieldName    Name of the field to use as a key.
     *                                This field must be unique according
     *                                to {@link DDefinition::isFieldUnique()}.
     *                                If <code>null</code>, returned results
     *                                will be non-associative.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the provided field name
     *                                            is not valid or unique.
     */
    public function useKey($fieldName = null)
    {
        // Check that field is valid.
        if ($fieldName !== null) {
            $definitionFields = $this->definition->getFields();
            if (!isset($definitionFields[ $fieldName ])) {
                throw new DInvalidParameterValueException(
                    'fieldName',
                    array(__CLASS__, __FUNCTION__),
                    'valid field name'
                );
            }
            if (!$this->definition->isFieldUnique($fieldName)) {
                throw new DInvalidParameterValueException(
                    'fieldName',
                    array(__CLASS__, __FUNCTION__),
                    'valid field name of a unique field'
                );
            }
        }
        $this->key = $fieldName;

        return $this;
    }

    /**
     * Exports the results of this search using
     * a {@link app::decibel::file::DExportFormat DExportFormat}.
     *
     * @param    DExportFormat $exportFormat The export format to use.
     * @param    string        $filename     The name of the exported file.
     *
     * @return    void
     * @throws    DOk
     */
    public function export(DExportFormat $exportFormat, $filename)
    {
        // Determine export column names.
        $columnNames = array();
        foreach ($this->fields as $select) {
            $fields = $this->getChainedFields($select->fieldName);
            $finalField = array_pop($fields);
            $columnNames = array_merge(
                $columnNames,
                $finalField->getExportColumns()
            );
        }
        // Retrieve data for the export.
        // This is done before the export starts in case there is an error.
        $rows = $this->getFields(true);
        // Create a stream to export to.
        $stream = new DOutputStream();
        // Perform the export.
        $exportFormat->startExport($stream, $columnNames);
        foreach ($rows as &$row) {
            // Flatten the row.
            $flatRow = $this->flattenRowForExport($row);
            // Add the flattened row to the export.
            $exportFormat->exportRow($flatRow);
        }
        $exportFormat->endExport();
        // Send to the browser.
        $response = new DOk();
        $response->addHeader('Content-type', 'text/csv; charset=utf-16');
        $response->addHeader('Content-disposition', "attachment; filename=\"{$filename}.csv\"");
        $response->setBody($stream);
        throw $response;
    }

    ///@cond INTERNAL
    /**
     * Flattens a row for export.
     *
     * @param    array $row       The row to flatten. The original variable
     *                            will not be modified.
     *
     * @return    array
     */
    protected function flattenRowForExport(array &$row)
    {
        $flatRow = array();
        foreach ($row as $key => &$value) {
            if (is_array($value)) {
                foreach ($value as $subKey => &$subValue) {
                    $flatRow[ $key . '_' . $subKey ] = $subValue;
                }
            } else {
                $flatRow[ $key ] = $value;
            }
        }

        return $flatRow;
    }
    ///@endcond
    /**
     * Returns a caching ID for this search with the specified flags.
     *
     * @param    array $fields List of {@link DFieldSelect} objects being
     *                                returned by this search.
     * @param    array $flags  List of string flags for the search.
     *
     * @return    string
     */
    protected function getCacheId($fields = array(), $flags = array())
    {
        // Determine the cache ID for each field included in the search results.
        $fieldCacheIds = array();
        foreach ($fields as $field) {
            /* @var $field DFieldSelect */
            $fieldCacheIds[] = $field->getCacheId();
        }
        // Serialize the search options and field cache IDs.
        $cacheId = md5(serialize($this) . implode('|', $fieldCacheIds));
        // Append any provided flags to the cache ID.
        if ($flags) {
            $cacheId .= '_' . implode('_', $flags);
        }

        return $cacheId;
    }

    /**
     * Returns the number of results found by this search.
     *
     * @code
     * use app\MyApp\Clients\Client;
     *
     * // How many clients are there?
     * $clientCount = Client::search()
     *        ->getCount();
     *
     * // How many clients are active?
     * $clientCount = Client::search()
     *        ->filterByField('active', true)
     *        ->getCount();
     * @endcode
     *
     * @return    integer
     */
    public function getCount()
    {
        return $this->getAbsoluteCount(true);
    }

    /**
     * Returns the definition of the model being searched.
     *
     * @return    DBaseModel_Definition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Returns the SQL executed by this search.
     *
     * @note
     * This method should be used for debugging purposes only.
     *
     * @return    string    The SQL, or <code>null</code> if this search hasn't
     *                    been executed yet.
     */
    public function getExecutedSql()
    {
        return $this->sql;
    }

    /**
     * Returns an array containing the requested field for this search.
     *
     * This list can be paginated by providing the two optional parameters.
     *
     * @code
     * use app\decibel\model\search\DBaseModelSearch;
     * use app\MyApp\Clients\Client;
     *
     * // Retrieve a list of client names.
     * $clientNames = Client::search()
     *        ->getField('name');
     *
     * // Retrieve a list of account manager IDs
     * // currently assigned to inactive clients.
     * $accountManagerIDs = Client::search()
     *        ->filterByField('active', false)
     *        ->getField('accountManager');
     *
     * // Retrieve a paginated list of account manager names
     * // currently assigned to inactive clients.
     * $accountManagerNames = Client::search()
     *        ->filterByField('active', false)
     *        ->getField(
     *            'accountManager',
     *            DBaseModelSearch::RETURN_STRING_VALUES,
     *            1,
     *            10
     *        );
     * @endcode
     *
     * @param    string $fieldName        The field to return.
     * @param    string $returnType       How field values will be returned. One of:
     *                                    - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                                    - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                                    - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     * @param    int    $pageNumber       If provided, results will be paginated
     *                                    starting from the specified page.
     * @param    int    $pageSize         The number of results per page,
     *                                    if pagination is being used.
     * @param    bool   $distinct         If <code>true</code>, only distinct
     *                                    field combinations will be returned.
     *
     * @return    ArrayAccess    An array containing model IDs as keys mapped to field
     *                        values, or a {@link app::decibel::utility::DPagination DPagination}
     *                        object if pagination options are provided.
     * @throws    DInvalidParameterValueException    If a paramter value is invalid.
     * @todo if distinct = true, useKey should be null
     */
    public function getField($fieldName, $returnType = DBaseModelSearch::RETURN_SERIALIZED,
                             $pageNumber = null, $pageSize = 10, $distinct = false)
    {
        if ($pageNumber !== null) {
            $executer = DPaginatedFieldSearchExecuter::decorate($this)
                                                     ->setPagination($pageNumber, $pageSize);
        } else {
            $executer = DFieldSearchExecuter::decorate($this);
        }

        return $executer
            ->setFieldName($fieldName)
            ->setReturnType($returnType)
            ->setDistinct($distinct)
            ->execute();
    }

    /**
     * Returns an array containing the requested fields for this search.
     *
     * This list can be paginated by providing the two optional parameters.
     *
     * @param    string $returnType       How field values will be returned. One of:
     *                                    - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                                    - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                                    - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     * @param    int    $pageNumber       If provided, results will be paginated
     *                                    starting from the specified page.
     * @param    int    $pageSize         The number of results per page,
     *                                    if pagination is being used.
     * @param    bool   $distinct         If <code>true</code>, only distinct
     *                                    field combinations will be returned.
     *
     * @return    array    A multi-dimensional array containing associative arrays
     *                    of field names and values. If the <code>ID</code> field
     *                    has been requested and no key has been specified through
     *                    the {@link DBaseModelSearch::useKey()} method, IDs will
     *                    be used as keys of the main array.
     * @todo if distinct = true, useKey should be null
     */
    public function getFields($returnType = DBaseModelSearch::RETURN_SERIALIZED,
                              $pageNumber = null, $pageSize = 10, $distinct = false)
    {
        if ($pageNumber !== null) {
            $executer = DPaginatedFieldsSearchExecuter::decorate($this)
                                                      ->setPagination($pageNumber, $pageSize);
        } else {
            $executer = DFieldsSearchExecuter::decorate($this);
        }

        return $executer
            ->setReturnType($returnType)
            ->setDistinct($distinct)
            ->execute();
    }

    /**
     * Returns the ids resulting from this search.
     *
     * This list can be paginated by providing the two optional parameters.
     *
     * @param    int $pageNumber      If provided, results will be paginated
     *                                starting from the specified page.
     * @param    int $pageSize        The number of results per page,
     *                                if pagination is being used.
     *
     * @return    array    List of object IDs.
     * @throws    DInvalidSearchException    If a group by has been added to the search.
     */
    public function getIds($pageNumber = null, $pageSize = 10)
    {
        // Paginate the search.
        if ($pageNumber !== null) {
            $executer = DPaginatedIdsSearchExecuter::decorate($this)
                                                   ->setPagination($pageNumber, $pageSize);
        } else {
            $executer = DIdsSearchExecuter::decorate($this);
        }

        return $executer->execute();
    }

    /**
     * Returns the ID returned at the specified index.
     *
     * If no parameters are specified, the first ID will be returned.
     *
     * @param    int $index The index of the ID to return.
     *
     * @return    int        The ID at the requested index, or <code>null</code>
     *                    if no ID exists    at that index.
     */
    public function getId($index = 0)
    {
        return DIdSearchExecuter::decorate($this)
                                ->setIndex($index)
                                ->execute();
    }

    /**
     * Returns a list of model instances based on the specified criteria.
     *
     * This list can be paginated by providing the two optional parameters.
     *
     * @param    int $pageNumber      If provided, results will be paginated
     *                                starting from the specified page.
     * @param    int $pageSize        The number of results per page,
     *                                if pagination is being used.
     *
     * @return    array    List of model instances. Keys will be non-associative
     *                    unless the {@link DBaseModelSearch::useKey()} method has
     *                    been called.
     */
    public function getObjects($pageNumber = null, $pageSize = 10)
    {
        // Paginate the search.
        if ($pageNumber !== null) {
            $executer = DPaginatedObjectsSearchExecuter::decorate($this)
                                                       ->setPagination($pageNumber, $pageSize);
        } else {
            $executer = DObjectsSearchExecuter::decorate($this);
        }

        return $executer->execute();
    }

    /**
     * Returns the object returned at the specified index.
     *
     * If no parameters are specified, the first object will be returned.
     *
     * @param    int $index The index of the object to return
     *
     * @return    DBaseModel    The specified object, or <code>null</code> if no object exists
     *                        at the specified index.
     */
    public function getObject($index = 0)
    {
        return DObjectSearchExecuter::decorate($this)
                                    ->setIndex($index)
                                    ->execute();
    }

    /**
     * Determines if this search would return any results if executed.
     *
     * @code
     * use app\MyApp\Clients\Client;
     *
     * // Are there any clients?
     * $clientsAvailable = Client::search()
     *        ->hasResults();
     *
     * // Is there a client with the name 'ACME'?
     * $clientCount = Client::search()
     *        ->filterByField('name', 'ACME')
     *        ->hasResults();
     * @endcode
     *
     * @return    bool
     */
    public function hasResults()
    {
        return (bool)$this->getAbsoluteCount(true);
    }

    /**
     * Orders joins for this search to ensure they are executed
     * in the correct sequence.
     *
     * @return    void
     */
    abstract protected function orderJoins();

    /**
     * Prepares to execute a search.
     *
     * This involves converting all search options into SQL pieces ready
     * to be compiled into a query.
     *
     * @return    void
     */
    protected function prepare()
    {
        if ($this->prepared) {
            return;
        }
        // Copy user added joins so these can be retained when cloning.
        $this->userJoins = $this->join;
        $this->prepareGroup();
        $this->prepareSort();
        // Add where conditions
        foreach ($this->conditions as $condition) {
            /* @var $condition DSearchCondition */
            $this->prepareSearchCondition($condition);
        }
        // Order joins based on the hierarchy of the model.
        $this->orderJoins();
        // Add joins.
        foreach ($this->join as $join) {
            /* @var $join DJoin */
            $this->joinSql[] = (string)$join;
            $where = $join->getWhere();
            if ($where) {
                $this->whereSql[] = $where;
            }
        }
        $this->prepared = true;
    }

    /**
     * Prepares sort conditions for the search, ensuring required joins are created.
     *
     * If no group by conditions have been added to the search,
     * the <code>id</code> field will be used to group results, ensuring
     * only one result per model can be returned regardless of joins.
     *
     * @return    void
     */
    protected function prepareGroup()
    {
        $this->applyDefaultGroup();
        // Build group conditions.
        foreach ($this->group as $groupCriteria) {
            /* @var $groupCriteria DGroupCriteria */
            $sql = $groupCriteria->getCriteria($this);
            if ($sql !== null) {
                $this->groupSql[] = $sql;
            }
        }
    }

    ///@cond INTERNAL
    /**
     * Prepares a {@link DSearchCondition} for this search.
     *
     * @param    DSearchCondition $searchCondition The search condition.
     *
     * @return    void
     */
    protected function prepareSearchCondition(DSearchCondition $searchCondition)
    {
        $condition = $searchCondition->getCondition($this);
        // Handle a single where condition.
        if (is_string($condition)) {
            $this->whereSql[] = $condition;
            // Or an array containing select, where and/or having.
        } else {
            if (isset($condition['select']) && $condition['select']) {
                $this->selectSql[] = $condition['select'];
            }
            if (isset($condition['where']) && $condition['where']) {
                $this->whereSql[] = $condition['where'];
            }
            if (isset($condition['having']) && $condition['having']) {
                $this->havingSql[] = $condition['having'];
            }
        }
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Prepares sort conditions for the search, ensuring required joins are created.
     *
     * @return    void
     */
    protected function prepareSort()
    {
        // Build sort conditions.
        foreach ($this->sort as $key => $sortCriteria) {
            /* @var $sortCriteria DSortCriteria */
            $sql = $sortCriteria->getCriteria($this);
            if ($sql !== null) {
                $this->sortSql[] = "{$sql} {$this->sortOrder[$key % count($this->sortOrder)]}";
            }
        }
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Performs a query against the database.
     *
     * @param    string $sql        The SQL to execute.
     * @param    array  $parameters Parameters to use for the query.
     *
     * @return    DQuery
     * @throws    DDatabaseException    If an error occurs while querying
     *                                the database.
     */
    protected function query($sql, array $parameters = array())
    {
        $query = new DQuery($sql, $parameters, $this->database);
        if ($this->debug) {
            debug($query);
        }

        return $query;
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Returns a list of objects based on the specified criteria.
     *
     * @param    array  $fields           Array containing field names to be
     *                                    included in the results, mapped to the
     *                                    aggregate function to be applied to
     *                                    each field. If not provided, all fields
     *                                    of the model will be returned.
     * @param    string $returnType       How field values will be returned. One of:
     *                                    - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                                    - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                                    - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     * @param    bool   $distinct         If <code>true</code>, only distinct
     *                                    field combinations will be returned.
     *
     * @return    array    Data rows returned from the query.
     */
    public function search(array $fields,
                           $returnType = DBaseModelSearch::RETURN_SERIALIZED,
                           $distinct = false)
    {
        // Handle deprecated return types.
        if ($returnType === true) {
            $returnType = DBaseModelSearch::RETURN_STRING_VALUES;
        } else {
            if ($returnType === false) {
                $returnType = DBaseModelSearch::RETURN_SERIALIZED;
            } else {
                $returnType = $returnType;
            }
        }
        // Check cache if not in debug mode and not a string values search.
        // String values searches are generally performed for exports
        // and may contain too much data to be cached within the memory limit.
        // @todo Find a better way to decide if something should be cached
        //       or not based on the actual size of data.
        $data = null;
        if ($this->cacheResults) {
            $flags = array($returnType);
            if ($distinct) {
                $flags[] = 'distinct';
            }
            $cacheId = $this->getCacheId($fields, $flags);
            $modelSearchCache = DModelSearchCache::load();
            $data = $modelSearchCache->retrieve($this->qualifiedName, $cacheId);
        }
        // If there is no cached value.
        if ($data === null) {
            $this->ids = array();
            $this->keys = array();
            try {
                $data = $this->executeSearch($fields, $returnType, $distinct);
                // Catch any query execution exceptions to allow execution
                // to continue, however log the issue and halt in debug mode.
            } catch (DQueryExecutionException $exception) {
                DErrorHandler::throwException($exception);

                return array();
            }
            // Store value in cache for next access.
            if ($this->cacheResults) {
                $cacheExpiry = $this->getCacheExpiry($this->ids);
                $modelSearchCache->set($this->qualifiedName, $cacheId, $data, $cacheExpiry);
            }
            // Extract IDs and keys from cached data.
        } else {
            $this->ids = array();
            $this->keys = array();
            foreach ($data as $row) {
                $this->storeResultKey($row);
            }
        }

        return $data;
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Creates an instance of a model based on the provided ID.
     *
     * @param    int $id ID of the model instance to create.
     *
     * @return    DBaseModel
     */
    protected function createObject($id)
    {
        $qualifiedName = $this->qualifiedName;

        return $qualifiedName::create((int)$id);
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Performs the query and returns a processed result set.
     *
     * @param    array  $fields           Array containing field names to be
     *                                    included in the results, mapped to the
     *                                    aggregate function to be applied to
     *                                    each field.
     * @param    string $returnType       How field values will be returned. One of:
     *                                    - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                                    - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                                    - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     * @param    bool   $distinct         If <code>true</code>, only distinct
     *                                    field combinations will be returned.
     *
     * @return    array
     * @throws    DQueryExecutionException
     */
    protected function executeSearch(array $fields, $returnType, $distinct)
    {
        $data = array();
        // Build the select SQL, allowing any joins
        // to be added before the search is prepared.
        $selectSql = $this->buildSelectSql($fields, $returnType);
        // Prepare query components for this search.
        $this->prepare();
        // Include any selects added while preparing.
        $selectSql = array_merge(
            $selectSql,
            $this->selectSql
        );
        // Build query SQL.
        $this->sql = $this->buildSql($selectSql, $distinct);
        // Run query.
        $query = $this->query($this->sql);
        while ($row = $query->getNextRow()) {
            // Convert values based on return type.
            foreach ($fields as $select) {
                /* @var $select DSelect */
                $select->processRow($this, $row, $returnType);
            }
            // Store the data and key.
            $data[] = $row;
            $this->storeResultKey($row);
        }

        return $data;
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Stores the ID and key for a search results row.
     *
     * @param    array $row The result row.
     *
     * @return    void
     */
    protected function storeResultKey($row)
    {
        if (isset($row['id'])) {
            $this->ids[] = (int)$row['id'];
        }
        // Store the key if there is one.
        if ($this->key) {
            $this->keys[] = $row[ $this->key ];
        }
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Returns the number of records that will be matched by the search.
     *
     * @param    bool $applyLimit Whether to apply any LIMIT clause.
     *
     * @return    int        Number of matching records
     */
    public function getAbsoluteCount($applyLimit = false)
    {
        // Check cache if not in debug mode.
        $result = null;
        if ($this->cacheResults) {
            $flags = array('count');
            if ($applyLimit) {
                $flags[] = 'limit';
            }
            $cacheId = $this->getCacheId(array(), $flags);
            $modelSearchCache = DModelSearchCache::load();
            $result = $modelSearchCache->retrieve($this->qualifiedName, $cacheId);
        }
        // If there is no cached value.
        if ($result === null) {
            // Prepare query components for this search.
            $this->prepare();
            // Build query SQL.
            $selectSql = array_merge(
                array($this->getAbsoluteCountSql() . ' AS `matchingRecords`'),
                $this->selectSql
            );
            // Ensure the the default `id` group otherwise no results
            // will be returned.
            $applyGroup = (count($this->group) > 0);
            $sql = $this->buildSql($selectSql, false, $applyGroup, false, $applyLimit);
            // Run query.
            try {
                $query = $this->query($sql);
                // For grouped queries, the matching records will be
                // the number or rows returned.
                if (count($this->group) > 0) {
                    $result = $query->getNumRows();
                } else {
                    $row = $query->getNextRow();
                    $result = (int)$row['matchingRecords'];
                }
                // Store value in cache for next access.
                if ($this->cacheResults) {
                    $cacheExpiry = $this->getCacheExpiry($this->validObjects);
                    $modelSearchCache->set($this->qualifiedName, $cacheId, $result, $cacheExpiry);
                }
                // Catch any query execution exceptions to allow execution
                // to continue, however report the issue and halt in debug mode.
            } catch (DQueryExecutionException $exception) {
                DErrorHandler::throwException($exception);
                $result = 0;
            }
        }

        return $result;
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Returns the SQL SELECT statement required to determine
     * the absolute count for this search.
     *
     * @return    string
     */
    protected function getAbsoluteCountSql()
    {
        return 'COUNT(*)';
    }
    ///@endcond
    /**
     * Removes all cached model search data for the specified model.
     *
     * @param    string $qualifiedName Qualified name of the model to remove.
     *
     * @return    void
     */
    public static function uncacheModel($qualifiedName)
    {
        DModelSearchCache::load()->clearQualifiedName($qualifiedName);
    }

    ///@cond INTERNAL
    /**
     * Allows a field value to be set using array syntax.
     *
     * @param    string $name  Name of the field.
     * @param    mixed  $value Field value.
     *
     * @return    void
     * @throws    DReadOnlyParameterException    This is an invalid action for the
     *                                        DBaseModelSearch object as search
     *                                        results are read-only.
     */
    public function offsetSet($name, $value)
    {
        DErrorHandler::throwException(new DReadOnlyParameterException($name, get_class($this)));
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Allows a field value to be returned to it's default value using array syntax.
     *
     * @param    string $name Name of the field.
     *
     * @return    void
     * @throws    DReadOnlyParameterException    This is an invalid action for the
     *                                        DBaseModelSearch object as search
     *                                        results are read-only.
     */
    public function offsetUnset($name)
    {
        DErrorHandler::throwException(new DReadOnlyParameterException($name, get_class($this)));
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Allows a field value to be returned using array syntax.
     *
     * @param    string $name Name of the field.
     *
     * @return    mixed    The field value.
     */
    public function offsetGet($name)
    {
        // Non-associative keys requested.
        if ($this->key === null) {
            $idPosition = $name;
            // No key specified, default behaviour to use IDs.
        } else {
            if ($this->key === false) {
                $idPosition = array_search($name, $this->ids);
                // Key specified.
            } else {
                $idPosition = array_search($name, $this->keys);
            }
        }

        return $this->createObject($this->ids[ $idPosition ]);
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Allows checking for the existence of a field using array syntax.
     *
     * @param    string $name Name of the field.
     *
     * @return    void
     */
    public function offsetExists($name)
    {
        // Non-associative keys requested.
        if ($this->key === null) {
            $exists = isset($this->ids[ $name ]);
            // No key specified, default behaviour to use IDs.
        } else {
            if ($this->key === false) {
                $exists = in_array($name, $this->ids);
                // Key specified.
            } else {
                $exists = in_array($name, $this->keys);
            }
        }

        return $exists;
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Returns the current model instance.
     *
     * @return    DModel
     */
    public function current()
    {
        return $this->currentModel;
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Returns the ID of the current model instance.
     *
     * @return    int
     */
    public function key()
    {
        // Non-associative keys requested.
        if ($this->key === null) {
            $key = $this->position;
            // No key specified, default behaviour to use IDs.
        } else {
            if ($this->key === false) {
                $key = $this->ids[ $this->position ];
                // Key specified.
            } else {
                $key = $this->keys[ $this->position ];
            }
        }

        return $key;
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Increments the internal iterator pointer.
     *
     * @return    void
     */
    public function next()
    {
        $this->currentModel = null;
        // Make sure all returned IDs are for valid models. This will protect
        // against any situation where a returned ID is for a model that has
        // subsequently been deleted, although this shouldn't really happen.
        do {
            ++$this->position;
            if (isset($this->ids[ $this->position ])) {
                try {
                    $this->currentModel = $this->createObject($this->ids[ $this->position ]);
                } catch (DUnknownModelInstanceException $exception) {
                    // Just move on to the next result if this isn't valid for some reason.
                }
            }
            // Keep going until we find a valid model or run out of IDs!
        } while ($this->currentModel === null
            && $this->position < count($this->ids));
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Rewinds the internal iterator pointer.
     *
     * @return    void
     */
    public function rewind()
    {
        if ($this->ids === null) {
            $this->ids = $this->getIds();
        }
        // Use the next method to initialise the position so that
        // the ID checking is invoked on the first model also.
        $this->position = -1;
        $this->next();
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Determines if the current iterator position is valid.
     *
     * @return    bool
     */
    public function valid()
    {
        return ($this->currentModel !== null);
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Returns an array of {@link app::decibel::model::field::DField DField}
     * objects for each of the fields used by this search.
     *
     * @param    mixed $fieldNames Chained field names.
     *
     * @return    array    List of {@link app::decibel::model::field::DField DField}
     *                    objects.
     * @throws    DInvalidParameterValueException    If any of the fields added
     *                                            to this search do not exist,
     *                                            or a linked search is attempted
     *                                            on a non-relational field.
     */
    public function getChainedFields($fieldNames)
    {
        // Normalise parameters
        $fieldNames = (array)$fieldNames;
        // Start with the definition for the searched object
        // as all searches must originate here.
        $definition = $this->getDefinition();
        $fieldCount = count($fieldNames);
        // Loop through the fields to be searched.
        $fields = array();
        for ($i = 0; $i < $fieldCount; ++$i) {
            $fieldName = $fieldNames[ $i ];
            $definitionFields = $definition->getFields();
            if (!isset($definitionFields[ $fieldName ])) {
                throw new DInvalidParameterValueException(
                    'fieldNames',
                    array(__CLASS__, __FUNCTION__),
                    "provided field {$fieldName} is not a valid field in the model {$definition->qualifiedName})"
                );
            }
            $field = $definitionFields[ $fieldName ];
            $fields[] = $field;
            // If this is no the last field, load the definition
            // for the object it links to.
            if ($i < $fieldCount - 1) {
                // All fields except for the last must be a relational field.
                if (!$field instanceof DRelationalField) {
                    throw new DInvalidParameterValueException(
                        'fieldNames',
                        array(__CLASS__, __FUNCTION__),
                        "provided field {$fieldName} is not a field of type app\decibel\model\field\DRelationalField"
                    );
                }
                // Handle special case for child objects "parent" field.
                // @todo Fix this in the child object!
                if ($field->addedBy === DChild::class
                    && $field->getName() === DChild::FIELD_PARENT
                ) {
                    $linkTo = $definition->getOption(DChild::OPTION_PARENT_OBJECT);
                } else {
                    $linkTo = $field->linkTo;
                }
                $definition = $linkTo::getDefinition();
            }
        }

        return $fields;
    }
    ///@endcond
    /**
     * Getter for retriving the next condition key.
     *
     * @return int
     */
    public function getNextConditionKey()
    {
        $current = $this->keyCounter;
        ++$this->keyCounter;

        return $current;
    }

    ///@cond INTERNAL
    /**
     * Implementation of count function for Countable interface.
     *
     * @return    int
     */
    public function count()
    {
        if ($this->ids === null) {
            $this->ids = $this->getIds();
        }

        return count($this->ids);
    }
    ///@endcond
}
