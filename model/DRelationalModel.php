<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\debug\DInvalidPropertyException;
use app\decibel\model\field\DField;
use app\decibel\model\field\DLinkedObjectsField;
use app\decibel\model\utilisation\DUtilisationRecord;
use app\decibel\registry\DClassQuery;
use app\decibel\utility\DResult;

/**
 * An implementation of the {@link DRelational} interface for classes extending {@link DModel}.
 *
 * @author        Timothy de Paris
 */
trait DRelationalModel
{
    /**
     * Checks the asset usage to determine if it can be deleted.
     *
     * Called on the {@link app::decibel::model::DBaseModel::ON_BEFORE_DELETE DBaseModel::ON_BEFORE_DELETE} event.
     *
     * @return    DResult
     */
    protected function checkUtilisation()
    {
        $canDelete = array();
        $qualifiedNames = DClassQuery::load()
                                     ->getClassNames();
        // Check utilisation index.
        $search = DUtilisationRecord::search()
                                    ->filterByField(DUtilisationRecord::FIELD_TO, $this->id)
                                    ->filterByField(self::FIELD_QUALIFIED_NAME, $qualifiedNames)
                                    ->filterByField(
                                        DUtilisationRecord::FIELD_MAINTAIN_INTEGRITY,
                                        true
                                    );
        if ($search->hasResults()) {
            $canDelete[] = "This {$this->displayName} is linked to by another object.";
        }

        return $canDelete;
    }

    /**
     * Determines if this model is linked to a specified model.
     *
     * @param    DBaseModel $search       The model to check for.
     * @param    string     $fieldName    The name of the field to look at.
     *                                    If omitted, all fields of type
     *                                    {@link app::decibel::model::field::DLinkedObjectsField DLinkedObjectsField}
     *                                    will be inspected.
     *
     * @return    bool
     * @throws    DInvalidPropertyException    If no field exists with the specified name.
     */
    public function isLinkedTo(DBaseModel $search, $fieldName = null)
    {
        $isLinked = false;
        $searchId = $search->getId();
        $fieldNames = $this->getLinkedFieldNames($fieldName);
        foreach ($fieldNames as $fieldName) {
            if (in_array($searchId, $this->fieldValues[ $fieldName ])) {
                $isLinked = true;
                break;
            }
        }

        return $isLinked;
    }

    /**
     * Returns a {@link DLinkedObjectsField} with the specified name.
     *
     * @param    string $name
     *
     * @return    DField
     * @throws    DInvalidPropertyException    If no {@link DLinkedObjectsField} exists
     *                                        with the specified name.
     */
    protected function getLinkedField($name)
    {
        $field = $this->getField($name);
        if (!$field instanceof DLinkedObjectsField) {
            throw new DInvalidPropertyException($name, get_class($this));
        }

        return $field;
    }

    /**
     * Returns a list containing the names of all {@link DLinkedObjectField}
     * fields for this model.
     *
     * @param    string $fieldName Optional field name to limit to.
     *
     * @return    array
     */
    protected function getLinkedFieldNames($fieldName = null)
    {
        $fieldNames = array_keys($this->getFieldsOfType(DLinkedObjectsField::class));
        if ($fieldName) {
            $fieldNames = array_intersect($fieldNames, array($fieldName));
        }

        return $fieldNames;
    }

    /**
     * Links this object to another object.
     *
     * @note
     * The model will not be saved by this method.
     *
     * @param    string $fieldName    The name of the field to add the link to.
     * @param    DModel $object       The object to link to.
     * @param    int    $position     The position of the newly linked object.
     *                                If ommitted, the link will be added to
     *                                the end of the list.
     *
     * @return    DResult    Whether the link was successfully added.
     * @throws    DInvalidPropertyException    If no {@link DLinkedObjectsField} exists
     *                                        with the specified name.
     */
    public function linkTo($fieldName, DModel $object, $position = null)
    {
        $result = new DResult($object->displayName, 'linked');
        // Ensure that the data array contains an array.
        set_default($this->fieldValues[ $fieldName ], array());
        // Check parameters.
        $field = $this->getField($fieldName);
        if (!$object instanceof $field->linkTo) {
            $result->setSuccess(false, 'The requested field cannot link to this type of object.');
            // Check that object is not already linked.
        } else {
            if (in_array($object->getId(), $this->fieldValues[ $fieldName ])) {
                $result->setSuccess(false,
                                    "This {$this->displayName} is already linked to the requested {$object->displayName}.");
            } else {
                // Check position.
                if ($position === null
                    || $position > sizeof($this->fieldValues[ $fieldName ])
                ) {
                    $position = sizeof($this->fieldValues[ $fieldName ]);
                }
                // Store original data.
                if (!isset($this->originalValues[ $fieldName ])) {
                    $this->originalValues[ $fieldName ] = $this->fieldValues[ $fieldName ];
                }
                // Add link.
                array_splice($this->fieldValues[ $fieldName ], $position, 0, array($object->getId()));
                unset($this->fieldPointers[ $fieldName ]);
            }
        }

        return $result;
    }

    /**
     * Removes a link to another object.
     *
     * @note
     * The model will not be saved by this method.
     *
     * @param    string $fieldName The name of the field to remove the link from.
     * @param    DModel $object    The object to remove.
     *
     * @return    DResult    Whether the link was successfully removed.
     * @throws    DInvalidPropertyException    If no {@link DLinkedObjectsField} exists
     *                                        with the specified name.
     */
    public function removeLink($fieldName, DModel $object)
    {
        $result = new DResult($object->displayName, 'unlinked');
        // Check that the field is valid.
        $this->getLinkedField($fieldName);
        // Check object exists and find it's position.
        $position = array_search(
            $object->getId(),
            $this->fieldValues[ $fieldName ]
        );
        if ($position === false) {
            $result->setSuccess(false, "No link exists to the specified {$object->displayName}.");
        } else {
            // Store original data.
            if (!isset($this->originalValues[ $fieldName ])) {
                $this->originalValues[ $fieldName ] = $this->fieldValues[ $fieldName ];
            }
            // Remove link.
            array_splice($this->fieldValues[ $fieldName ], $position, 1);
            unset($this->fieldPointers[ $fieldName ]);
        }

        return $result;
    }
}
