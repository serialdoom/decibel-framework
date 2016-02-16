<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

/**
 * Provides information about an element comprising a database table.
 *
 * @author        Timothy de Paris
 */
abstract class DTableElementDefinition extends DSchemaElementDefinition
{
    /**
     * The definition of the table to which this element belongs.
     *
     * @var        DTableDefinition
     */
    protected $table;

    /**
     * Creates a new {@link DTableElementDefinition} object.
     *
     * @param    DTableDefinition $table The table to which this element belongs.
     *
     * @return    static
     */
    public function __construct(DTableDefinition $table = null)
    {
        parent::__construct();
        $this->table = $table;
    }

    /**
     * Returns the {@link DTableDefinition} to which this column belongs.
     *
     * @return    DTableDefinition
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Sets the {@link DTableDefinition} to which this column belongs.
     *
     * @param    DTableDefinition $table The table definition.
     *
     * @return    static    This instance, for chaining.
     */
    public function setTable(DTableDefinition $table)
    {
        $this->table = $table;

        return $this;
    }
}
