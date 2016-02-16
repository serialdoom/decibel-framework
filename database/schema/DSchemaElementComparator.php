<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\database\debug\DInvalidComparisonException;
use app\decibel\server\DServer;

/**
 * Provides functionality to compare this {@link DDefinableSchemaElement} instance with
 * another {@link DDefinableSchemaElement} of the same type.
 *
 * @author    Timothy de Paris
 */
abstract class DSchemaElementComparator implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * The element to compare with.
     *
     * @var        DSchemaElementDefinition
     */
    protected $compareTo;

    /**
     * Compares two schema element names to determine if they are different.
     *
     * @note
     * Comparison is performed differently depending on the operating
     * system on which Decibel is running. For Windows operating systems,
     * the comparison is not case-sensitive. On *nix operating systems,
     * a case-sensitive comparison is performed.
     *
     * @param    string $a The first name.
     * @param    string $b The second name.
     *
     * @return    bool    <code>true</code> if the names are the same,
     *                    <code>false</code> if they are not.
     */
    public static function compareNames($a, $b)
    {
        if (DServer::isWindows()) {
            $result = (strtolower($a) === strtolower($b));
        } else {
            $result = ($a === $b);
        }

        return $result;
    }

    /**
     * Specifies the schema element to which comparison will be made.
     *
     * @param    DSchemaElementDefinition $compareTo          The element to compare with.
     * @param    bool                     $ignoreNames        If <code>true</code>, differences in the
     *                                                        schema element names will be ignored
     *                                                        and no exception thrown.
     *
     * @return    static    This instance, for chaining.
     * @throws    DInvalidComparisonException    If the provided schema element is not of the same
     *                                        type as the adapted schema element.
     */
    public function compareTo(DSchemaElementDefinition $compareTo, $ignoreNames = false)
    {
        // Check that we are comparing the same type of element.
        $firstClass = get_class($this->adaptee);
        $secondClass = get_class($compareTo);
        if ($firstClass !== $secondClass) {
            throw new DInvalidComparisonException("Incompatible classes: <code>{$firstClass}</code> and <code>{$secondClass}</code>");
        }
        // Check that the names match, if required.
        if (!$ignoreNames) {
            $nameA = $this->adaptee->getName();
            $nameB = $compareTo->getName();
            if (!static::compareNames($nameA, $nameB)) {
                throw new DInvalidComparisonException("Incompatible column names: <code>{$nameA}</code> and <code>{$nameB}</code>");
            }
        }
        $this->compareTo = $compareTo;

        return $this;
    }

    /**
     * Determines if the compared schema elements are different.
     *
     * @return    bool
     */
    abstract public function hasChanges();
}
