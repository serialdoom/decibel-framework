<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

/**
 * Allows CSV (comma seperated values) files to be merged.
 *
 * @author    Timothy de Paris
 */
class DCsvMerger
{
    /**
     * The name of the field on which CSVs will be merged.
     *
     * @var        string
     */
    protected $primaryKey;
    /**
     * Whether rows in the second CSV with no matching primary key
     * in the first CSV will be included.
     *
     * @var        bool
     */
    protected $rightJoin;

    /**
     * Creates a new CSV merger.
     *
     * @param    string $primaryKey   The name of the field on which the CSVs
     *                                will be merged.
     * @param    bool   $rightJoin    If <code>true</code>, rows in the second
     *                                CSV with no matching primary key
     *                                in the first CSV will be included.
     *
     * @return    static
     */
    public function __construct($primaryKey, $rightJoin = false)
    {
        $this->primaryKey = $primaryKey;
        $this->rightJoin = $rightJoin;
    }

    /**
     * Generates a header from the provided CSV.
     *
     * @param    array  $csv        The CSV to generate a header for.
     * @param    string $primaryKey The primary key.
     *
     * @return    array
     */
    protected function generateHeader(array $csv, $primaryKey)
    {
        $header = array();
        for ($i = 0; $i < count($csv[ DCsvFile::KEY_HEADER ]); $i++) {
            if ($csv[ DCsvFile::KEY_HEADER ][ $i ] !== $primaryKey) {
                $header[ $csv[ DCsvFile::KEY_HEADER ][ $i ] ] = '';
            }
        }

        return $header;
    }

    /**
     * Indexes the provided CSV.
     *
     * @param    array  $csv        The CSV to index.
     * @param    string $primaryKey The primary key.
     *
     * @return    array
     */
    protected function generateIndex(array $csv, $primaryKey)
    {
        $index = array();
        for ($i = 0; $i < count($csv) - 1; $i++) {
            $index[ $csv[ $i ][ $primaryKey ] ] = $i;
        }

        return $index;
    }

    /**
     * Generates the key list for the provided indexes.
     *
     * @param    array $index1 The first index.
     * @param    array $index2 The second index.
     *
     * @return    array
     */
    protected function generateKeyList(array $index1, array $index2)
    {
        if ($this->rightJoin) {
            $keyList = array_keys(array_merge($index1, $index2));
        } else {
            $keyList = array_keys($index1);
        }

        return $keyList;
    }

    /**
     * Merges two CSV arrays into a single array.
     *
     * @note
     * The primary key will be the first column in the returned CSV data.
     *
     * @param    array $csv1 The first CSV.
     * @param    array $csv2 The second CSV.
     *
     * @return    array    The merged CSV.
     */
    public function merge(array $csv1, array $csv2)
    {
        $primaryKey = $this->primaryKey;
        // Index both arrays.
        $index1 = $this->generateIndex($csv1, $primaryKey);
        $index2 = $this->generateIndex($csv2, $primaryKey);
        // Generate key list.
        $keyList = $this->generateKeyList($index1, $index2);
        // Generate header arrays for each array
        $header1 = $this->generateHeader($csv1, $primaryKey);
        $header2 = $this->generateHeader($csv2, $primaryKey);
        // Create merged array with new header row.
        // As the primary key is not included in either
        // header it will need to be added.
        $mergedArray = array(
            DCsvFile::KEY_HEADER => array_merge(
                array($primaryKey),
                array_keys($header1),
                array_keys($header2)
            ),
        );
        // Merge arrays.
        for ($i = 0; $i < count($keyList); $i++) {
            // Check for key in first array.
            if (isset($index1[ $keyList[ $i ] ])) {
                $mergeTemp1 = $csv1[ $index1[ $keyList[ $i ] ] ];
            } else {
                $mergeTemp1 = $header1;
            }
            // Check for key in second array.
            if (isset($index2[ $keyList[ $i ] ])) {
                $mergeTemp2 = $csv2[ $index2[ $keyList[ $i ] ] ];
            } else {
                $mergeTemp2 = $header2;
            }
            // Add new record to merged array.
            $mergedArray[] = array_merge($mergeTemp1, $mergeTemp2);
        }

        return $mergedArray;
    }
}
