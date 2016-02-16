<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\router;

use app\decibel\event\DEvent;
use app\decibel\http\error\DHttpError;
use app\decibel\regional\DLabel;

/**
 * Event triggered when a {@link app::decibel::http::error::DHttpError DHttpError}
 * is sent to the client.
 *
 * @author    Mafzal Afzal
 */
class DOnHttpError extends DEvent
{
    /**
     * The triggered HTTP response.
     *
     * @var        DHttpError
     */
    private $error;

    /**
     * Create an instance of the event.
     *
     * @return    static
     */
    final public function __construct(DHttpError $error)
    {
        parent::__construct();
        $this->error = $error;
    }

    /**
     * Gets the HTTP response for this event.
     *
     * @return    DHttpError
     */
    public function getError()
    {
        return $this->error;
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
