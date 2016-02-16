<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\utility\DDate;
use DateTime;
use DateTimeZone;

/**
 * Provides functionality for managing the timezone of a user.
 *
 * @author        Timothy de Paris
 */
class DUserTimezone implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DUser::class;
    }

    /**
     * Returns the timezone in which the user is located.
     *
     * @return    string    A valid PHP timezone identifier.
     * @see        http://php.net/datetimezone.listidentifiers
     */
    public function getTimeZone()
    {
        $timezone = $this->adaptee->getFieldValue(DUser::FIELD_TIMEZONE);
        if (!$timezone) {
            $timezone = DECIBEL_REGIONAL_TIMEZONE;
        }

        return $timezone;
    }

    /**
     * Returns the time zone for this user, represented as a string.
     *
     * @return    string
     */
    public function getTimeZoneString()
    {
        $timezone = $this->adaptee->getFieldValue(DUser::FIELD_TIMEZONE);
        if ($timezone) {
            $timezoneString = DDate::timeZoneToString($timezone);
        } else {
            $timezoneString = DDate::getApplicationDefaultTimeZoneString();
        }

        return $timezoneString;
    }

    /**
     * Returns the UTC offset as a number of hours for this user.
     *
     * @return    int
     */
    public function getTimeZoneOffset()
    {
        $timezone = $this->adaptee->getFieldValue(DUser::FIELD_TIMEZONE);
        if ($timezone) {
            $timezone = new DateTimeZone($timezone);
            $time = new DateTime('now', $timezone);
            $offset = ($timezone->getOffset($time) / 3600);
        } else {
            $offset = DDate::getTimeZoneOffset();
        }

        return $offset;
    }

    /**
     * Returns the UTC offset for this user, formatted as a string.
     *
     * @return    string
     */
    public function getTimeZoneOffsetString()
    {
        $timezone = $this->adaptee->getFieldValue(DUser::FIELD_TIMEZONE);
        if ($timezone) {
            $timezone = new DateTimeZone($timezone);
            $time = new DateTime('now', $timezone);
            $offset = ($timezone->getOffset($time) / 3600);
        } else {
            $offset = DDate::getTimeZoneOffset();
        }
        if ($offset > 0) {
            $offsetString = sprintf('UTC +%d', $offset);
        } else {
            if ($offset < 0) {
                $offsetString = sprintf('UTC %d', $offset);
            } else {
                $offsetString = 'UTC';
            }
        }

        return $offsetString;
    }
}
