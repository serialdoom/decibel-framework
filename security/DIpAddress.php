<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\security;

use app\decibel\cache\DPublicCache;
use app\decibel\http\request\DRequest;
use app\decibel\model\DLightModel;
use app\decibel\model\event\DModelEvent;
use app\decibel\regional\DLabel;
use app\decibel\utility\DResult;

/**
 * Represents an IP address that is known to the Decibel security system.
 *
 * @author    Andrzej Kus
 */
class DIpAddress extends DLightModel
{
    /**
     * 'Description' field name.
     *
     * @var        string
     */
    const FIELD_DESCRIPTION = 'description';

    /**
     * 'Flag' field name.
     *
     * @var        string
     */
    const FIELD_FLAG = 'flag';

    /**
     * 'IP Address' field name.
     *
     * @var        string
     */
    const FIELD_IP_ADDRESS = 'ipAddress';

    /**
     * IP address flag for a unknown IP address.
     *
     * This flag can be provided as a parameter to the {@link DIpAddress::checkIpAddress()} method.
     *
     * @var        int
     */
    const FLAG_UNKNOWN = 0;

    /**
     * IP address flag for a trusted IP address.
     *
     * This flag can be provided as a parameter to the {@link DIpAddress::checkIpAddress()} method.
     *
     * @var        int
     */
    const FLAG_TRUSTED = 1;

    /**
     * IP address flag for a known IP address.
     *
     * This flag can be provided as a parameter to the {@link DIpAddress::checkIpAddress()} method.
     *
     * @var        int
     */
    const FLAG_KNOWN = 2;

    /**
     * IP address flag for a blocked IP address.
     *
     * This flag can be provided as a parameter to the {@link DIpAddress::checkIpAddress()} method.
     *
     * @var        int
     */
    const FLAG_BLOCKED = 3;

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
        parent::uncache($event);
        // Clear any cached IP checks.
        if (isset($this->originalValues[ self::FIELD_IP_ADDRESS ])) {
            $this->clearIpAddressCache();
        }
    }

    /**
     * Checks whether a specified IP address matches a flag.
     *
     * This method can be used to determine whether a particular IP address
     * is flagged as blocked, known or secure in the Decibel Security Console.
     *
     * @code
     * use app\decibel\security\DIpAddress;
     *
     * if (DIpAddress::checkIpAddress('127.0.0.1', DIpAddress::FLAG_TRUSTED)) {
     *    // Do something secure!
     * }
     * @endcode
     *
     * @note
     * Use the {@link app::decibel::http::request::DRequest::getIpAddress() DRequest::getIpAddress()}
     * method to retrieve the current client IP address.
     *
     * This method caches results in the {@link app::decibel::cache::DPublicCache DPublicCache}
     *
     * @param    string $ipAddress    The IP address to check.
     * @param    int    $flag         The flag to match. One of:
     *                                - {@link DIpAddress::FLAG_UNKNOWN}
     *                                - {@link DIpAddress::FLAG_KNOWN}
     *                                - {@link DIpAddress::FLAG_TRUSTED}
     *                                - {@link DIpAddress::FLAG_BLOCKED}
     *
     * @return    bool
     */
    public static function checkIpAddress($ipAddress, $flag)
    {
        // Check whether this is available in the cache.
        $publicCache = DPublicCache::load();
        $invalidatorKey = DRequest::class . '-' . $ipAddress;
        $key = "{$ipAddress}_{$flag}";
        $check = $publicCache->retrieve($invalidatorKey, $key);
        // If not, perform the check.
        if ($check === null) {
            $check = DIpAddress::search()
                               ->filterByField(self::FIELD_IP_ADDRESS, $ipAddress)
                               ->filterByField(self::FIELD_FLAG, $flag)
                               ->limitTo(1)
                               ->hasResults();
            // Store in the cache.
            $publicCache->set($invalidatorKey, $key, $check);
        }

        return $check;
    }

    /**
     * Removes any cached data for this IP address.
     *
     * @return    void
     */
    protected function clearIpAddressCache()
    {
        $publicCache = DPublicCache::load();
        $ipAddress = $this->getFieldValue(self::FIELD_IP_ADDRESS);
        $invalidatorKey = DRequest::class . '-' . $ipAddress;
        $publicCache->remove($invalidatorKey);
    }

    /**
     * Returns the flag for this IP address.
     *
     * @return    int        One of:
     *                    - {@link DIpAddress::FLAG_UNKNOWN}
     *                    - {@link DIpAddress::FLAG_KNOWN}
     *                    - {@link DIpAddress::FLAG_TRUSTED}
     *                    - {@link DIpAddress::FLAG_BLOCKED}
     */
    public function getFlag()
    {
        return $this->getFieldValue(self::FIELD_FLAG);
    }

    /**
     * Returns a list of available flags for IP addresses.
     *
     * @return    array
     */
    public static function getFlagOptions()
    {
        return array(
            DIpAddress::FLAG_TRUSTED => new DLabel(self::class, 'trusted'),
            DIpAddress::FLAG_KNOWN   => new DLabel(self::class, 'known'),
            DIpAddress::FLAG_BLOCKED => new DLabel(self::class, 'blocked'),
        );
    }

    ///@cond INTERNAL
    /**
     * Calculates the string representation of this model.
     *
     * @return    string
     */
    public function getStringValue()
    {
        return $this->getFieldValue(self::FIELD_IP_ADDRESS);
    }
    ///@endcond
    /**
     * Sets the flag for this IP address.
     *
     * @param    int $flag        One of:
     *                            - {@link DIpAddress::FLAG_UNKNOWN}
     *                            - {@link DIpAddress::FLAG_KNOWN}
     *                            - {@link DIpAddress::FLAG_TRUSTED}
     *                            - {@link DIpAddress::FLAG_BLOCKED}
     *
     * @return    void
     */
    public function setFlag($flag)
    {
        $this->setFieldValue(self::FIELD_FLAG, $flag);
    }

    /**
     * Checks that the provided information is able to be saved.
     *
     * @return    DResult
     */
    protected function validateIpAddress()
    {
        $result = new DResult();
        $request = DRequest::load();
        $flag = $this->getFieldValue(self::FIELD_FLAG);
        // Can't block the current IP address.
        if ($this->getFieldValue(self::FIELD_IP_ADDRESS) === $request->getIpAddress()
            && $flag === DIpAddress::FLAG_BLOCKED
        ) {
            $result->setSuccess(false, new DLabel(self::class, 'unableToBlock'));
        } else {
            if (!DECIBEL_CORE_BLOCKIPS
                && $flag === DIpAddress::FLAG_BLOCKED
            ) {
                $result->addMessage(new DLabel(self::class, 'blockingDisabled'));
            } else {
                $result = null;
            }
        }

        return $result;
    }
}
