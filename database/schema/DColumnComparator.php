<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

/**
 * Provides comparison functionality for column definitions.
 *
 * @author        Timothy de Paris
 */
class DColumnComparator extends DSchemaElementComparator
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DColumnDefinition::class;
    }

    /**
     * Determines if the compared schema elements are different.
     *
     * @return    bool
     */
    public function hasChanges()
    {
        $valuesA = $this->adaptee->getFieldValues();
        $valuesB = $this->compareTo->getFieldValues();
        unset($valuesA[ DColumnDefinition::FIELD_NAME ]);
        unset($valuesB[ DColumnDefinition::FIELD_NAME ]);

        return (serialize($valuesA) !== serialize($valuesB));
    }
}
