<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\index;

use app\decibel\database\mysql\DMySQL;
use app\decibel\model\DBaseModel;
use app\decibel\model\field\DField;
use app\decibel\model\field\DStringField;
use app\decibel\model\search\DBaseModelSearch;
use app\decibel\regional\DLabel;
use app\decibel\utility\DResult;

/**
 * Represents a unique database index required by a model.
 *
 * @author        Timothy de Paris
 */
class DUniqueIndex extends DIndex
{
    /**
     * Name of the label representing missing value in the language.
     *
     * @var        string
     *
     */
    const LABEL_VALUE_ALREADY_EXIST_MESSAGE = 'message';

    /**
     * Adds a field to this index.
     *
     * @note
     * Text fields added to a {@link DUniqueIndex} cannot be longer than 1000 characters.
     *
     * @param    DField $field Pointer to the {@link app::decibel::model::field::DField DField} object
     *                            describing the field to add.
     *
     * @return    void
     * @throws    DInvalidIndexFieldException    If the field cannot be added to this index.
     */
    public function addField(DField $field)
    {
        // Validate field addition.
        if ($field instanceof DStringField
            && $field->getMaxLength() > 1000
        ) {
            throw new DInvalidIndexFieldException(
                $this,
                $field,
                'Text fields longer than 1000 characters are not valid for this index type.'
            );
        }

        return parent::addField($field);
    }

    /**
     * Returns the index type name as used by the database.
     *
     * @return    string
     */
    public function getDatabaseIdentifier()
    {
        return DMySQL::INDEX_TYPE_UNIQUE;
    }

    /**
     * Builds a search object that can be used to validate this index.
     *
     * @param    DBaseModel $model The model to retrieve the search for.
     *
     * @return    DBaseModelSearch
     */
    protected function getUniqueSearch(DBaseModel $model)
    {
        // Perform a search using each of the fields in the index.
        $indexedObject = get_class($model);
        $uniqueSearch = $indexedObject::search()
                                      ->setDatabase($model->getDatabase())
                                      ->removeDefaultFilters()
                                      ->ignore(array($model));
        foreach (array_keys($this->fields) as $fieldName) {
            $uniqueSearch->filterByField(
                $fieldName,
                $model->getFieldValue($fieldName)
            );
        }

        return $uniqueSearch;
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
        $result = new DResult();
        // Retrieve a search that can detect if this index is unique.
        $uniqueSearch = $this->getUniqueSearch($model);
        // If any object is returned, the unique index cannot be validated.
        if ($uniqueSearch->hasResults()) {
            $existingObject = $uniqueSearch->getObject();
            if ($existingObject) {
                $fields = array_keys($this->fields);
                $firstField = array_shift($fields);
                $message = new DLabel(
                    DUniqueIndex::class,
                    DUniqueIndex::LABEL_VALUE_ALREADY_EXIST_MESSAGE,
                    array(
                        'existingObjectDisplayName' => $existingObject->displayName,
                        'fieldNamesStringValue'     => $this->getFieldNamesString(),
                    )
                );
                $result->setSuccess(false, $message, $firstField);
                $existingObject->free();
            }
        }

        return $result;
    }
}
