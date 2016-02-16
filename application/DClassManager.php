<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\application;

use app\decibel\model\DModel;
use app\decibel\registry\DClassQuery;
use app\decibel\registry\DInvalidClassNameException;

///@cond INTERNAL
/**
 * DClassManager is responsible for managing information about all available
 * classes within the core framework and installed Apps.
 *
 * @author        Timothy de Paris
 * @deprecated    In favour of {@link DClassQuery}
 */
final class DClassManager
{
    /**
     * Returns an array containing the qualified name of each registered class
     * that extends the specified ancestor.
     *
     * @param    string $ancestor     Qualified name of the ancestor class.
     *                                Defaults to {@link app::decibel::model::DModel DModel}
     * @param    bool   $leafOnly     If true, only leaf classes will be returned
     *                                (i.e. classes that are not extended).
     *
     * @return    array
     * @throws    DInvalidClassNameException    If the requested ancestor
     *                                        class does not exist.
     * @deprecated    In favour of {@link DClassQuery::getClassNames()}
     */
    public static function getClasses($ancestor = DModel::class,
                                      $leafOnly = false)
    {
        $filter = DClassQuery::FILTER_CONCRETE;
        if ($leafOnly) {
            $filter |= DClassQuery::FILTER_LEAF;
        }

        return DClassQuery::load()
                          ->setAncestor($ancestor)
                          ->setFilter($filter)
                          ->getClassNames();
    }

    /**
     * Tests a provided qualified class name to determine if this class
     * exists on the current installation.
     *
     * @param    string $qualifiedName The class name to test.
     * @param    string $ancestor      The class ancestor.
     *
     * @return    bool
     * @deprecated
     */
    public static function isValidClassName($qualifiedName, $ancestor = null)
    {
        if ($qualifiedName === $ancestor) {
            $valid = true;
        } else {
            $query = DClassQuery::load()
                                ->setFilter();
            if ($ancestor !== null) {
                $query->setAncestor($ancestor);
            }
            $valid = $query->isValid($qualifiedName);
        }

        return $valid;
    }
}
///@endcond
