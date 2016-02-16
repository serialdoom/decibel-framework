<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise\event;

use app\decibel\model\event\DModelEvent;
use app\decibel\model\field\DLinkedObjectField;
use app\decibel\regional\DLabel;

/**
 * Event triggered when a user change password of their account.
 *
 * @author        Timothy de Paris
 */
class DOnPasswordChange extends DModelEvent
{
    /**
     * Defines parametermeters available for this event.
     *
     * @return    void
     */
    protected function define()
    {
        $user = new DLinkedObjectField('user', new DLabel(self::class, 'user'));
        $user->setLinkTo(DUser::class);
        $this->addField($user);
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
