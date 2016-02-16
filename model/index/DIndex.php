<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\index;

use app\decibel\database\mysql\DMySQL;
use app\decibel\model\DBaseModel;
use app\decibel\model\field\DField;
use app\decibel\utility\DBaseClass;
use app\decibel\utility\DResult;
use app\decibel\utility\DString;

/**
 * Represents a database index required by a model.
 *
 * @author        Timothy de Paris
 */
class DIndex
{
    use DBaseClass;
    /**
     * The name of this index.
     *
     * @var        string
     */
    protected $name;
    /**
     * The human-readable name of this index.
     *
     * @var        string
     */
    protected $displayName;
    /**
     * Array of pointers to the DField objects describing
     * the fields included in this index.
     *
     * @var        array
     */
    protected $fields;

    /**
     * Creates a new DIndex.
     *
     * @param    string $name        The name of the index.
     * @param    string $displayName Optional human-readable name of the index.
     *
     * @return    static
     */
    public function __construct($name, $displayName = null)
    {
        $this->name = $name;
        $this->displayName = $displayName;
        $this->fields = array();
    }

    /**
     * Adds a field to this index.
     *
     * @param    DField $field Pointer to the object describing the field to add.
     *
     * @return    static
     * @throws    DInvalidIndexFieldException    If the field cannot be added to this index.
     */
    public function addField(DField $field)
    {
        $this->fields[ $field->getName() ] = $field;
        // Sort alphabetically by field name.
        ksort($this->fields);

        return $this;
    }

    /**
     * Returns the index type name as used by the database.
     *
     * @return    string
     */
    public function getDatabaseIdentifier()
    {
        return DMySQL::INDEX_TYPE_STANDARD;
    }

    /**
     * Returns the human-readable name of this index.
     *
     * @return    string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Returns a list of display names for fields included in this index,
     * formatted as a string.
     *
     * @return    string
     */
    public function getFieldNamesString()
    {
        // Build list of field display names for any error messages.
        $displayNameList = array();
        foreach ($this->fields as $field) {
            /* @var $field DField */
            $displayNameList[] = $field->getDisplayName();
        }

        return DString::implode($displayNameList, ', ', ' and ');
    }

    /**
     * Returns a list of fields assigned to this index.
     *
     * @note
     * The returned array will be indexed by field names,
     * and sorted alphabetically by field name.
     *
     * @return    array    List of {@link app::decibel::model::field::DField DField}
     *                    objects.
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns the name of this index.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns a list of native field assigned to this index.
     *
     * @note
     * The returned array will be sorted alphabetically by field name.
     *
     * @return    array    List of field names.
     */
    public function getNativeFieldNames()
    {
        $fields = array();
        foreach ($this->fields as $fieldName => $field) {
            /* @var $field DField */
            if ($field->isNativeField()) {
                $fields[] = $fieldName;
            }
        }

        return $fields;
    }

    /**
     * Checks the supplied model does not violate any conditions placed
     * on the database by this index.
     *
     * @param    DBaseModel $model The model that requested validation.
     *
     * @return    DResult
     */
    public function validate(DBaseModel $model = null)
    {
        return null;
    }
}
