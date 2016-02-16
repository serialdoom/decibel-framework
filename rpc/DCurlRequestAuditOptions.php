<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc;

use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DBooleanField;
use app\decibel\model\field\DTextField;
use app\decibel\utility\DUtilityData;

/**
 * Specifies options for the auditing of a {@link DCurlRequest}.
 *
 * @author    Timothy de Paris
 */
class DCurlRequestAuditOptions extends DUtilityData
{
    /**
     * 'Log Successful' field name.
     *
     * @var        string
     */
    const FIELD_LOG_SUCCESSFUL = 'logSuccessful';
    /**
     * 'Post Body Mask' field name.
     *
     * @var        string
     */
    const FIELD_POST_BODY_MASK = 'postBodyMask';
    /**
     * 'Response' field name.
     *
     * @var        string
     */
    const FIELD_RESPONSE_MASK = 'responseMask';

    /**
     * Creates a new {@link DCurlRequestAuditOptions}.
     *
     * @return    static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Applies a mask to the provided content.
     *
     * @param    string $mask    The mask to apply.
     * @param    string $content Content to mask.
     *
     * @return    string
     */
    protected function applyMask($mask, $content)
    {
        if (!$mask) {
            return $content;
        }
        // Run the regular expression to see if any content needs to be masked.
        $matches = null;
        preg_match_all($mask, $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            // For each match, mask matched sub-patterns.
            $fullMatch = array_shift($match);
            $maskedMatch = $fullMatch;
            foreach ($match as $toMask) {
                $maskedMatch = str_replace(
                    $toMask,
                    str_repeat('*', strlen($toMask)),
                    $maskedMatch
                );
            }
            // Take the masked match and replace this into the post body.
            $content = str_replace(
                $fullMatch,
                $maskedMatch,
                $content
            );
        }

        return $content;
    }

    /**
     * Applies the post body mask to the provided content.
     *
     * @param    string $postBody The post body to mask.
     *
     * @return    string
     */
    public function applyPostBodyMask($postBody)
    {
        $postBodyMask = $this->getFieldValue(self::FIELD_POST_BODY_MASK);

        return $this->applyMask($postBodyMask, $postBody);
    }

    /**
     * Applies the response mask to the provided content.
     *
     * @param    string $response The response to mask.
     *
     * @return    string
     */
    public function applyResponseMask($response)
    {
        $responseMask = $this->getFieldValue(self::FIELD_RESPONSE_MASK);

        return $this->applyMask($responseMask, $response);
    }

    /**
     * Defines fields available for this utility data object
     *
     * @return    void
     */
    protected function define()
    {
        $logSuccessful = new DBooleanField(self::FIELD_LOG_SUCCESSFUL, 'Log Successful');
        $logSuccessful->setDescription('<p>If enabled, both successful and failed requests will be logged.</p><p>If disabled, only failed requests will be logged.</p>');
        $this->addField($logSuccessful);
        $postBodyMask = new DTextField(self::FIELD_POST_BODY_MASK, 'Post Body Mask');
        $postBodyMask->setNullOption('None');
        $postBodyMask->setDescription('<p>Regular expression mask to be applied to the post body before auditing.</p><p>Any matched sub-patterns within this expression will be masked before the post body is logged.</p>');
        $this->addField($postBodyMask);
        $responseMask = new DTextField(self::FIELD_RESPONSE_MASK, 'Response Mask');
        $responseMask->setNullOption('None');
        $responseMask->setDescription('<p>Regular expression mask to be applied to the response before auditing.</p><p>Any matched sub-patterns within this expression will be masked before the response is logged.</p>');
        $this->addField($responseMask);
    }

    /**
     * Determines whether successful requests should be logged,
     * or only failures.
     *
     * @return    bool
     */
    public function getLogSuccessful()
    {
        return $this->getFieldValue(self::FIELD_LOG_SUCCESSFUL);
    }

    /**
     * Sets whether successful requests should be logged,
     * or only failures.
     *
     * By default, all requests will be logged.
     *
     * @note
     * Failed requests are those where the remote server replies or returns a
     * non-200 HTTP status code. It is possible for the API to return
     * a failure message using a 200 HTTP status code, this type of failure
     * will not be logged if successful request logging is disabled.
     *
     * @param    bool $logSuccessful      If <code>true</code>, both successful
     *                                    and failed requests will be logged.
     *                                    If <code>false</code>, only failed
     *                                    requests will be logged.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the provided value is not valid
     *                                        for the field.
     */
    public function setLogSuccessful($logSuccessful)
    {
        $this->setFieldValue(self::FIELD_LOG_SUCCESSFUL, $logSuccessful);

        return $this;
    }

    /**
     * Sets the post body mask.
     *
     * @param    string $postBodyMask     Regular expression mask to be applied
     *                                    to the post body before auditing.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the provided value is not valid
     *                                        for the field.
     */
    public function setPostBodyMask($postBodyMask)
    {
        $this->setFieldValue(self::FIELD_POST_BODY_MASK, $postBodyMask);

        return $this;
    }

    /**
     * Sets the response mask.
     *
     * @param    string $responseMask     Regular expression mask to be applied
     *                                    to the response before auditing.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the provided value is not valid
     *                                        for the field.
     */
    public function setResponseMask($responseMask)
    {
        $this->setFieldValue(self::FIELD_RESPONSE_MASK, $responseMask);

        return $this;
    }
}
