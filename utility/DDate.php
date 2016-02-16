<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use DateTime;
use DateTimeZone;

/**
 * Date class
 *
 * @author    Timothy de Paris
 */
class DDate
{
    /**
     * Mapping of time periods.
     *
     * @var        array
     */
    private static $timePeriods = array(
        'seconds' => 60,
        'minutes' => 60,
        'hours'   => 24,
        'days'    => 7,
        'weeks'   => 52,
        'years'   => 0,
    );
    /**
     * Mapping of time periods to seconds.
     *
     * @var        array
     */
    private static $timePeriodsSeconds = array(
        'year'   => 31536000,
        'month'  => 2592000,
        'day'    => 86400,
        'hour'   => 3600,
        'minute' => 60,
        'second' => 1,
    );
    /**
     * List of server time zones, cached here after first
     * call to {@link DDate::getTimeZones()}.
     *
     * @var        array
     */
    private static $timeZones;

    /**
     * Returns the amount of time passed between two times, rounded to the
     * nearest unit and formatted as a string.
     *
     * @param    int $from        Timestamp representing the 'from' time.
     * @param    int $to          Timestamp representing the 'to' time. If not
     *                            specified, the current time will be used.
     *
     * @return    String    String representing the time passed
     */
    public static function getTimePassed($from, $to = null)
    {
        if ($to === null) {
            $to = time() + 1;
        }
        $difference = ($to - $from);
        foreach (DDate::$timePeriodsSeconds as $period => $value) {
            if ($difference >= $value) {
                return ($difference / $value >= 2)
                    ? sprintf('%d&nbsp;%ss', ($difference / $value), $period)
                    : sprintf('1&nbsp;%s', $period);
            }
        }
    }

    /**
     * Returns a list of available timezones, segmented by continent.
     *
     * @return    array
     */
    public static function getTimeZones()
    {
        if (self::$timeZones === null) {
            self::$timeZones = array();
            foreach (DateTimeZone::listIdentifiers() as $timezone) {
                // Break into continent and region and ignore non-geographical zones.
                $parts = explode('/', $timezone);
                if (count($parts) == 1) {
                    continue;
                }
                $continent = array_shift($parts);
                $region = str_replace('_', ' ', implode(' - ', $parts));
                self::$timeZones[ $continent ][ $timezone ] = $region;
            }
        }

        return self::$timeZones;
    }

    /**
     * Returns the default time zone for the application.
     *
     * @return    string
     */
    public static function getApplicationDefaultTimeZone()
    {
        if (DECIBEL_REGIONAL_TIMEZONE) {
            $timezone = DECIBEL_REGIONAL_TIMEZONE;
        } else {
            $timezone = self::getServerDefaultTimeZone();
        }

        return $timezone;
    }

    /**
     * Returns the default time zone for the application, formatted as a string.
     *
     * @return    string
     */
    public static function getApplicationDefaultTimeZoneString()
    {
        if (defined('DECIBEL_REGIONAL_TIMEZONE')
            && DECIBEL_REGIONAL_TIMEZONE
        ) {
            $timezone = self::timeZoneToString(DECIBEL_REGIONAL_TIMEZONE);
        } else {
            $timezone = self::getServerDefaultTimeZoneString();
        }

        return $timezone;
    }

    /**
     * Returns the default time zone for the server.
     *
     * @return    string
     */
    public static function getServerDefaultTimeZone()
    {
        $timezone = ini_get('date.timezone');
        if (!$timezone) {
            $timezone = 'UTC';
        }

        return $timezone;
    }

    /**
     * Returns the default time zone for the server, formatted as a string.
     *
     * @return    string
     */
    public static function getServerDefaultTimeZoneString()
    {
        $timezone = ini_get('date.timezone');
        if ($timezone) {
            $timezone = self::timeZoneToString($timezone);
        } else {
            $timezone = 'UTC';
        }

        return $timezone;
    }

    /**
     * Sets the time zone for the application.
     *
     * @param    string $timezone The new time zone.
     *
     * @return    void
     */
    public static function setTimeZone($timezone)
    {
        if ($timezone) {
            date_default_timezone_set($timezone);
        }
    }

    /**
     * Returns the current time zone for the application, formatted as a string.
     *
     * @return    void
     */
    public static function getTimeZoneString()
    {
        return self::timeZoneToString(date_default_timezone_get());
    }

    /**
     * Returns the UTC offset as a number of hours
     * for the current application time zone.
     *
     * @note
     * This method considers daylight saving transitions for the timezone.
     *
     * @param    int $time        UNIX timestamp for which the offset should
     *                            be calaculated. If not provided, the current
     *                            time will be used.
     *
     * @return    int
     */
    public static function getTimeZoneOffset($time = null)
    {
        $timezone = new DateTimeZone(date_default_timezone_get());
        // Create a representation of the time to retrieve an offset for.
        $timeObject = new DateTime();
        if ($time !== null) {
            $timeObject->setTimestamp($time);
        }

        return ($timezone->getOffset($timeObject) / 3600);
    }

    /**
     * Returns the UTC offset as a number of hours
     * for the current application time zone.
     *
     * @note
     * This method considers daylight saving transitions for the timezone.
     *
     * @param    int $time        UNIX timestamp for which the offset should
     *                            be calaculated. If not provided, the current
     *                            time will be used.
     *
     * @return    int
     */
    public static function getTimeZoneOffsetString($time = null)
    {
        $offset = DDate::getTimeZoneOffset($time);
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

    /**
     * Returns the specified time zone, formatted as a string.
     *
     * @param    string $timezone The time zone.
     *
     * @return    string
     */
    public static function timeZoneToString($timezone)
    {
        $parts = explode('/', $timezone);

        return str_replace('_', ' ', implode(' - ', $parts));
    }

    /**
     * Converts a number of seconds into a human readable string.
     *
     * @param    int $seconds       The number of seconds.
     * @param    int $decimalPlaces The number of decimal places to round to.
     *
     * @return    string
     */
    public static function secondsToString($seconds, $decimalPlaces = 1)
    {
        $names = array_keys(self::$timePeriods);
        $multipliers = array_values(self::$timePeriods);
        $count = count(self::$timePeriods);
        if ($seconds < 0) {
            $positive = false;
            $seconds = abs($seconds);
        } else {
            $positive = true;
        }
        $i = 0;
        while ($seconds >= $multipliers[ $i ] && ($i < $count - 1)) {
            $seconds /= $multipliers[ $i ];
            $i++;
        }
        // Determine string format.
        if ($i == 0 || fmod($seconds, 1) == 0) {
            $format = '%0.0f %s';
        } else {
            $format = '%0.' . $decimalPlaces . 'f %s';
        }

        return sprintf($format, ($positive ? $seconds : ($seconds * -1)), $names[ $i ]);
    }

    /**
     * Returns the format for displaying date values, based on regional
     * configuration options.
     *
     * The format string can be used in any PHP date functions.
     *
     * @return    string
     */
    public static function getDateFormat()
    {
        return str_replace(
            array('dd', 'mm', 'yy'),
            array('d', 'm', 'Y'),
            DECIBEL_REGIONAL_DATEFORMAT
        );
    }

    /**
     * Returns the format for displaying date time values, based on regional
     * configuration options.
     *
     * The format string can be used in any PHP date functions.
     *
     * @return    string
     */
    public static function getDateTimeFormat()
    {
        $format = str_replace(
            array('dd', 'mm', 'yy'),
            array('d', 'm', 'Y'),
            DECIBEL_REGIONAL_DATEFORMAT
        );

        return "{$format} H:i";
    }
}
