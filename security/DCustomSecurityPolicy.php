<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\security;

use app\decibel\regional\DLabel;
use app\decibel\security\DSecurityPolicy;

/**
 * DCustomSecurityPolicy allows complete customisation of Decibel security
 * settings.
 *
 * @author        Timothy de Paris
 */
class DCustomSecurityPolicy extends DSecurityPolicy
{
    /**
     * Returns a description of this security policy.
     *
     * @return    DLabel
     */
    public function getDescription()
    {
        return new DLabel(self::class, 'description');
    }

    /**
     * Returns the name of this security policy.
     *
     * @return    DLabel
     */
    public function getName()
    {
        return new DLabel(self::class, 'name');
    }

    /**
     * Sets the default values for this security policy.
     *
     * @return    void
     */
    protected function initialise()
    { }
}
