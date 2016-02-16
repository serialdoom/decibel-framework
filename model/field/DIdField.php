<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\field\DNumericField;

/**
 * Represents a field that can contain the id of an object.
 *
 * @author        Timothy de Paris
 */
class DIdField extends DNumericField
{
    /**
     * The number of bytes used to store object ids.
     *
     * @var        int
     */
    const SIZE = 8;

    /**
     * Returns the default value for this type of field.
     *
     * This value will be used if no default value is supplied for the field.
     *
     * @return    string
     */
    public function getStandardDefaultValue()
    {
        return null;
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        $this->exportable = false;
        $this->randomisable = false;
        $this->readOnly = true;
        $this->size = DIdField::SIZE;
        $this->unsigned = true;
    }

    /**
     * Sets whether data for this field is exportable.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    bool $exportable Whether data for this field is exportable.
     *
     * @return    void
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function setExportable($exportable)
    {
        throw new DReadOnlyParameterException('exportable', $this->name);
    }

    /**
     * Sets whether data for this field can be randomised for testing purposes.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    bool $randomisable       Whether data for this field
     *                                    can be randomised.
     *
     * @return    void
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function setRandomisable($randomisable)
    {
        throw new DReadOnlyParameterException('randomisable', $this->name);
    }

    /**
     * Sets whether data for this field can be modified.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    bool $readOnly Whether the field data can be modified.
     *
     * @return    void
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function setReadOnly($readOnly)
    {
        throw new DReadOnlyParameterException('readOnly', $this->name);
    }

    /**
     * Sets the maximum size (in bytes) of data that can be stored
     * against this field.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    int $size Number of bytes (1, 2, 3, 4 or 8).
     *
     * @return    void
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function setSize($size)
    {
        throw new DReadOnlyParameterException('size', $this->name);
    }

    /**
     * Sets whether data for this field can be signed.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    bool $unsigned Whether data for this field can be signed.
     *
     * @return    void
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function setUnsigned($unsigned)
    {
        throw new DReadOnlyParameterException('unsigned', $this->name);
    }
}
