<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\model\field\DNumericField;

/**
 * Represents a field that can contain an integer value.
 *
 * @author        Timothy de Paris
 */
class DIntegerField extends DNumericField
{
    ///@cond INTERNAL
    /**
     * Start integer (for drop-down selection).
     *
     * @var        int
     */
    protected $start;

    ///@endcond
    ///@cond INTERNAL
    /**
     * End integer (for drop-down selection).
     *
     * @var        int
     */
    protected $end;

    ///@endcond
    ///@cond INTERNAL
    /**
     * Increasing step for the above drop-down.
     *
     * @var        int
     */
    protected $step;

    ///@endcond
    ///@cond INTERNAL
    /**
     * A human-readable suffix describing the integer value.
     *
     * @var        string
     */
    protected $suffix;

    ///@endcond
    /**
     * Returns the data type used by this field with PHP.
     *
     * @return    string
     */
    public function getInternalDataType()
    {
        return 'integer';
    }

    /**
     * Returns a regular expression that can be used to match the string
     * version of data for this field.
     *
     * @return    string    A regular expression, or <code>null</code> if it is
     *                    not possible to match via a regular expression.
     */
    public function getRegex()
    {
        return '[0-9]+';
    }

    /**
     * Returns the default value for this type of field.
     *
     * This value will be used if no default value is supplied for the field.
     *
     * @return    int
     */
    public function getStandardDefaultValue()
    {
        if ($this->nullOption === null
            && $this->start !== null
        ) {
            $defaultValue = $this->start;
        } else {
            $defaultValue = parent::getStandardDefaultValue();
        }

        return $defaultValue;
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        $this->size = 4;
        $this->unsigned = false;
        $this->step = 1;
    }

    /**
     * Sets the ending value for this field.
     *
     * @param    int $end The ending value.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setEnd($end)
    {
        $this->setInteger('end', $end);

        return $this;
    }

    /**
     * Sets the starting value for this field.
     *
     * @param    int $start The starting value.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setStart($start)
    {
        $this->setInteger('start', $start);

        return $this;
    }

    /**
     * Sets the step value for this field.
     *
     * @param    int $step The step value.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setStep($step)
    {
        $this->setInteger('step', $step);

        return $this;
    }

    /**
     * Sets a human-readable suffix describing the integer value, for example "miles".
     *
     * @param    string $suffix
     *
     * @return    static
     */
    public function setSuffix($suffix)
    {
        $this->setString('suffix', $suffix);

        return $this;
    }
}
