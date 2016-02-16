<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\security;

use app\decibel\auditing\DAuditRecord;
use app\decibel\model\field\DTextField;
use app\decibel\regional\DLabel;
use app\decibel\http\request\DRequest;

/**
 * Stores information about requests forbidden by the Web Application Firewall.
 *
 * @author        Timothy de Paris
 */
final class DForbiddenRequestLog extends DAuditRecord
{
    /**
     * 'IP Address' field name.
     *
     * @var        string
     */
    const FIELD_IP_ADDRESS = 'ipAddress';

    /**
     * 'Reason' field name.
     *
     * @var        string
     */
    const FIELD_REASON = 'reason';

    /**
     * 'URL' field name.
     *
     * @var        string
     */
    const FIELD_URL = 'url';

    /**
     * Defines fields and indexes required by this audit record.
     *
     * @return    void
     */
    public function define()
    {
        $labelUnknown = new DLabel('app\\decibel', 'unknown');
        // Set field information.
        $ipAddress = new DTextField(self::FIELD_IP_ADDRESS, 'IP Address');
        $ipAddress->setNullOption($labelUnknown);
        $ipAddress->setMaxLength(15);
        $this->addField($ipAddress);
        $url = new DTextField(self::FIELD_URL, 'Request URL');
        $url->setNullOption($labelUnknown);
        $url->setMaxLength(255);
        $this->addField($url);
        $reason = new DTextField(self::FIELD_REASON, 'Reason');
        $reason->setNullOption($labelUnknown);
        $reason->setMaxLength(255);
        $this->addField($reason);
    }

    /**
     * Returns the requested URL.
     *
     * Used to populate default field values.
     *
     * @note
     * Invalid characters are converted to their ASCII equivalent,
     * so a BACKSPACE character will be replaced with "{ASCII:127}".
     *
     * @return    string
     */
    protected static function getRequestUrl()
    {
        $request = DRequest::load();
        $pageUrl = (string)$request->getUrl();
        $invalidChars = DRequest::getInvalidUriChars();
        foreach (str_split($invalidChars) as $invalidChar) {
            $pageUrl = str_replace($invalidChar, '{ASCII:' . ord($invalidChar) . '}', $pageUrl);
        }

        return $pageUrl;
    }

    /**
     * Sets default field values for new model instances.
     *
     * @return    void
     */
    protected function setDefaultValues()
    {
        parent::setDefaultValues();
        $request = DRequest::load();
        $this->setFieldValue(self::FIELD_IP_ADDRESS, $request->getIpAddress());
        $this->setFieldValue(self::FIELD_URL, self::getRequestUrl());
    }
}
