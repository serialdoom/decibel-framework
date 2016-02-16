<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DInvalidParameterValueException;

/**
 * Implementation of the {@link DRandomisable} interface for {@link DField} objects.
 *
 * @author    Timothy de Paris
 */
trait DRandomisableField
{
    /**
     * Determines if this field can have randomised content created for
     * it for testing purposes.
     *
     * @var        bool
     */
    protected $randomisable = true;

    /**
     * Returns a random value suitable for assignment as the value of this field.
     *
     * @return    mixed
     */
    public function getRandomValue()
    {
        if ($this->isNativeField()) {
            switch ($this->getDataType()) {
                case DField::DATA_TYPE_TINYINT:
                case DField::DATA_TYPE_SMALLINT:
                case DField::DATA_TYPE_MEDIUMINT:
                case DField::DATA_TYPE_INT:
                case DField::DATA_TYPE_BIGINT:
                    $value = rand(0, 100);
                    break;
                case DField::DATA_TYPE_FLOAT:
                    $value = rand(10, 1000) / 10;
                    break;
                default:
                    $value = null;
                    break;
            }
        } else {
            $value = null;
        }

        return $value;
    }

    /**
     * Determines if the content of this field can be randomised when creating
     * randomised model instances.
     *
     * @return    bool
     */
    public function isRandomisable()
    {
        return $this->randomisable;
    }

    /**
     * Sets whether data for this field can be randomised for testing purposes.
     *
     * @param    bool $randomisable       Whether data for this field
     *                                    can be randomised.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setRandomisable($randomisable)
    {
        $this->setBoolean('randomisable', $randomisable);

        return $this;
    }
}
