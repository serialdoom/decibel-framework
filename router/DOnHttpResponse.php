<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\router;

use app\decibel\event\DEvent;
use app\decibel\http\DHttpResponse;
use app\decibel\regional\DLabel;

/**
 * Event triggered when an HTTP response is sent to the client.
 *
 * @author    Mafzal Afzal
 */
class DOnHttpResponse extends DEvent
{
    /**
     * The triggered HTTP response.
     *
     * @var        DHttpResponse
     */
    private $response;

    /**
     * Create an instance of the event.
     *
     * @return    static
     */
    final public function __construct(DHttpResponse $response)
    {
        parent::__construct();
        $this->response = $response;
    }

    /**
     * Gets the HTTP response for this event.
     *
     * @return    DHttpResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns a human-readable description for the configurable object.
     *
     * @return    DLabel
     */
    public static function getDescription()
    {
        return new DLabel(self::class, 'description');
    }

    /**
     * Returns a human-readable name for the configurable object.
     *
     * @return    DLabel
     */
    public static function getDisplayName()
    {
        return new DLabel(self::class, 'name');
    }
}
