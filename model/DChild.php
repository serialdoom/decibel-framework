<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\authorise\DAuthorisationManager;
use app\decibel\database\DQuery;
use app\decibel\model\DChildReflection;
use app\decibel\model\DModel;
use app\decibel\model\event\DModelEvent;
use Exception;

/**
 * The DChild class provides functionality to simplify the creation
 * of objects that are created and maintained within another object.
 *
 * @author        Timothy de Paris
 */
abstract class DChild extends DModel
{
    /**
     * 'Parent' field name.
     *
     * @var        string
     */
    const FIELD_PARENT = 'parent';

    /**
     * 'Parent Field Name' field name.
     *
     * @var        string
     */
    const FIELD_PARENT_FIELD = 'child_parentField';

    /**
     * 'Position' field name.
     *
     * @var        string
     */
    const FIELD_POSITION = 'child_position';

    /**
     * Specified the object class that these objects will be assigned to.
     * A string value in the form <code>[App Name]::[Object Name]</code> is required.
     *
     * @var        string
     */
    const OPTION_PARENT_OBJECT = 'parentObjectName';

    /**
     * Performs any uncaching operations neccessary when a model's data is changed to ensure
     * consitency across the application.
     *
     * @param    DModelEvent $event The event that required uncaching of the model.
     *
     * @return    void
     */
    public function uncache(DModelEvent $event = null)
    {
        // Uncache the parent of this object if deleting
        // or creating a new child.
        $parent = $this->getFieldValue(self::FIELD_PARENT);
        if (($event === DModel::ON_DELETE
                || (isset($this->originalData['id']) && $this->originalData['id'] === 0))
            && $parent !== null
        ) {
            // If the parent has been deleted, causing deletion of this child,
            // an exception will be thrown.
            try {
                // If this has been invoked by the parent being saved, don't
                // re-load the parent, as this may clear original data from
                // subsequent child fields, therefore deleting that data.
                // The parent will reload itself at the end of the save process.
                if (!$parent->hasUnsavedChanges()) {
                    $parent->loadFromDatabase($parent->getId(), true);
                }
            } catch (Exception $e) {
            }
        }
        parent::uncache($event);
    }

    /**
     * Returns the name for a privilege for this object with the provided suffix.
     *
     * If the privilege suffix provided is not valid for this object, null
     * will be returned.
     *
     * @param    string $suffix   The privilege suffix. A suffix must be comprised
     *                            of an upper case letter followed by one or more
     *                            lower case letters.
     *
     * @return    string
     */
    public function getPrivilegeName($suffix)
    {
        switch (strtolower($suffix)) {
            case 'create':
            case 'edit':
                $name = null;
                break;
            default:
                $name = parent::getPrivilegeName($suffix);
                break;
        }

        return $name;
    }

    /**
     * Provides a reflection of this class.
     *
     * @return    DChildReflection
     */
    public static function getReflection()
    {
        return new DChildReflection(get_called_class());
    }

    /**
     * Ensure no extraneous records exist in the object tables
     * within the database.
     *
     * Called on the <code>app\\decibel\\database\\DDatabase-optimise</code> event.
     *
     * @return    void
     */
    public static function cleanDatabase()
    {
        $user = DAuthorisationManager::getUser();
        // Clean up orphaned child objects.
        $query = new DQuery('app\\decibel\\model\\DChild-getOrphans');
        while ($row = $query->getNextRow()) {
            try {
                $object = DModel::create($row['id']);
                $object->delete($user);
            } catch (Exception $e) {
            }
        }
    }
}
