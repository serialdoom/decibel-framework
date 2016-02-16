<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

/**
 * Provides comparison functionality for index definitions.
 *
 * @author        Timothy de Paris
 */
class DIndexComparator extends DSchemaElementComparator
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DIndexDefinition::class;
    }

    /**
     * Determines if the compared schema elements are different.
     *
     * @return    bool
     */
    public function hasChanges()
    {
        $typeA = $this->adaptee->getFieldValue(DIndexDefinition::FIELD_TYPE);
        $typeB = $this->compareTo->getFieldValue(DIndexDefinition::FIELD_TYPE);
        $columnsA = implode(',', $this->adaptee->getColumns());
        $columnsB = implode(',', $this->compareTo->getColumns());

        return ($typeA !== $typeB
            || $columnsA !== $columnsB);
    }
}
