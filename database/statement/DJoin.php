<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\statement;

/**
 * Provides information about an SQL join.
 *
 * @author        Timothy de Paris
 */
class DJoin extends DStatementComponent
{
    /**
     * The join alias.
     *
     * @var        string
     */
    protected $alias;
    /**
     * Name of the table being joined to.
     *
     * @var        string
     */
    protected $table;
    /**
     * 'ON' clause for the join.
     *
     * @var        string
     */
    protected $on;
    /**
     * Type of the join.
     *
     * @var        string
     */
    protected $type;
    /**
     * Additional where clause.
     *
     * @var        string
     */
    protected $where;

    /**
     * Creates a new {@link DJoin} object.
     *
     * @param    string $table Name of the table being joined to.
     * @param    string $on    ON condition for the join.
     * @param    string $alias Alias for the join, if any.
     * @param    string $where WHERE condition for the join, if any.
     *
     * @return    static
     */
    public function __construct($table, $on, $alias = null, $where = null)
    {
        $this->table = trim($table, '`');
        $this->on = $on;
        $this->alias = trim($alias, '`');
        $this->where = $where;
    }

    /**
     * Returns this join as SQL.
     *
     * @return    string
     */
    public function __toString()
    {
        $alias = $this->getAlias();
        if ($alias !== $this->table) {
            $alias = " AS `{$alias}`";
        } else {
            $alias = '';
        }

        return " JOIN `{$this->table}`{$alias} ON {$this->on}";
    }

    /**
     * Returns the alias that will be used for this join.
     *
     * @return    string
     */
    public function getAlias()
    {
        if ($this->alias) {
            $alias = $this->alias;
        } else {
            $alias = $this->table;
        }

        return $alias;
    }

    /**
     * Returns the name of the table being joined to.
     *
     * @return    string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Returns the WHERE clause for this join.
     *
     * @return    string
     */
    public function getWhere()
    {
        return $this->where;
    }
}
