<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

use app\decibel\configuration\DConfiguration;
use app\decibel\model\field\DIntegerField;

/**
 * Configuration class for Decibel task functionality.
 *
 * @author    Timothy de Paris
 */
class DTaskConfiguration extends DConfiguration
{
    /**
     * 'Nightly Time' field name.
     *
     * @var        string
     */
    const FIELD_NIGHTLY_TIME = 'nightlyTime';

    /**
     * Defines the fields available for this configuration.
     *
     * @return    void
     */
    protected function define()
    {
        $nightlyTime = new DIntegerField(self::FIELD_NIGHTLY_TIME, 'Nightly Task Hour');
        $nightlyTime->setDescription('<p>Hour to run the nightly process, usually 4 a.m.</p>');
        $nightlyTime->setDefault(4);
        $nightlyTime->setStart(0);
        $nightlyTime->setEnd(23);
        $nightlyTime->setStep(1);
        $this->addField($nightlyTime);
    }

    /**
     * Returns the the hour at which nightly tasks will be run.
     *
     * @return    string
     */
    public function getNightlyTaskHour()
    {
        return $this->getFieldValue(self::FIELD_NIGHTLY_TIME);
    }
}
