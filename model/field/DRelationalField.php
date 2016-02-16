<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DNotImplementedException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\debug\DUnknownModelInstanceException;
use app\decibel\model\DModel;
use app\decibel\model\field\DField;

/**
 * Represents a field that can contain a relationship between this object
 * and other objects.
 *
 * @author        Timothy de Paris
 */
abstract class DRelationalField extends DField
{
    /**
     * No referential integrity will be maintained for this relationship.
     *
     * @var        int
     */
    const RELATIONAL_INTEGRITY_NONE = 0;

    /**
     * Any model linked to by this relationship will not be able to be deleted
     * while this relationship exists.
     *
     * @var        int
     */
    const RELATIONAL_INTEGRITY_REFERENTIAL = 1;

    /**
     * Deleting the model linked to by this relationship will trigger deletion
     * of the model that holds this relationship.
     *
     * @var        int
     * @todo    Implement this.
     */
    const RELATIONAL_INTEGRITY_DELETE = 2;

    /**
     * List of constants available for relational integrity modes.
     *
     * Used for validating provided relational integrity values.
     *
     * @var        array
     */
    protected static $relationIntegrityConstants = array(
        self::RELATIONAL_INTEGRITY_NONE        => 'app\\decibel\\model\\field\\DRelationalField::RELATIONAL_INTEGRITY_NONE',
        self::RELATIONAL_INTEGRITY_REFERENTIAL => 'app\\decibel\\model\\field\\DRelationalField::RELATIONAL_INTEGRITY_REFERENTIAL',
        //		self::RELATIONAL_INTEGRITY_DELETE			=> 'app\\decibel\\model\\field\\DRelationalField::RELATIONAL_INTEGRITY_DELETE',
    );

    /**
     * The type of referential integrity that will be applied to this field.
     *
     * @var        int
     */
    protected $relationalIntegrity;

    /**
     * Whether the model that holds this relationship will be cleared from the
     * cache when the linked to model is changed.
     *
     * @var        bool
     */
    protected $cacheIntegrity = false;

    /**
     * If specified, this message will be displayed when an integrity check
     * fails on object deletion.
     *
     * If not specified, the default message will be shown.
     *
     * @var        string
     */
    protected $integrityMessage;

    /**
     * Option specifying the type of object that this field will link to.
     *
     * The value of this option must be a qualified model name or an interface
     * name. If an interface name is provided, all models extending {@link DModel}
     * and implementing this interface will be included in the list.
     *
     * @var        string
     */
    protected $linkTo = null;

    /**
     * Option specifying the any objects that should not be able to be linked
     * to from this field.
     *
     * The value of this option must be an array of object IDs.
     *
     * @var        array
     */
    protected $ignore = array();

    ///@cond INTERNAL
    /**
     * Handles setting of field options.
     *
     * @param    string $name  The name of the option to set.
     * @param    mixed  $value The new value.
     *
     * @return    void
     * @throws    DInvalidPropertyException        If the parameter does not exist.
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function __set($name, $value)
    {
        // Check that supplied option is valid.
        switch ($name) {
            case 'relationalIntegrity':
                $this->setRelationalIntegrity($value);

                return;
            case 'ignore':
                return $this->setArray($name, $value);
            default:
                return parent::__set($name, $value);
        }
    }
    ///@endcond
    /**
     * Returns a model instance for the specified ID.
     *
     * @param    int $id Model instance ID.
     *
     * @return    DBaseModel    The model instance, or <code>null</code>
     *                        if no model instance exists with the specified ID.
     * @throws    DUnknownModelInstanceException
     */
    public function getInstanceFromId($id)
    {
        $qualifiedName = $this->linkTo;
        if ($qualifiedName
            && method_exists($qualifiedName, 'create')
        ) {
            $instance = $qualifiedName::create($id);
            // Fallback for DChild::parent field,
            // which currently doesn't have a linkTo attribute.
            // TODO: Add linkTo attribute to DChild::parent field
        } else {
            $instance = DModel::create($id);
        }

        return $instance;
    }

    /**
     * Returns the display name of the linked model.
     *
     * @return    string
     */
    public function getLinkDisplayName()
    {
        $displayName = 'link';
        $model = $this->linkTo;
        if (method_exists($model, 'getDisplayName')) {
            try {
                $displayName = $model::getDisplayName();
            } catch (DNotImplementedException $exception) {
            }
        }

        return $displayName;
    }

    /**
     * Returns the plural display name of the linked model.
     *
     * @return    string
     */
    public function getLinkDisplayNamePlural()
    {
        $displayNamePlural = 'links';
        $model = $this->linkTo;
        if (method_exists($model, 'getDisplayNamePlural')) {
            try {
                $displayNamePlural = $model::getDisplayNamePlural();
            } catch (DNotImplementedException $exception) {
            }
        }

        return $displayNamePlural;
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        $this->relationalIntegrity = self::RELATIONAL_INTEGRITY_REFERENTIAL;
        $this->integrityMessage = null;
    }

    /**
     * Sets the message to display when relational integrity is breached
     * for this field.
     *
     * @param    string $message      Message to display when relational
     *                                integrity is breached.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setIntegrityMessage($message)
    {
        $this->setString('integrityMessage', $message);

        return $this;
    }

    /**
     * Sets the type of model this field will link to.
     *
     * @param    string $linkTo   Qualified name of the model class
     *                            or interface this field will link to.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setLinkTo($linkTo)
    {
        if (!class_exists($linkTo)
            && !interface_exists($linkTo)
        ) {
            throw new DInvalidParameterValueException(
                'linkTo',
                array(__CLASS__, __FUNCTION__),
                'valid qualified model or interface name'
            );
        }
        $this->linkTo = $linkTo;

        return $this;
    }

    /**
     * Sets the relational integrity type for this field.
     *
     * @param    int $type Relational integrity type.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setRelationalIntegrity($type)
    {
        $this->setEnum(
            'relationalIntegrity',
            $type,
            self::$relationIntegrityConstants
        );

        return $this;
    }
}
