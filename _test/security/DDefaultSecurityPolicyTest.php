<?php
namespace tests\app\decibel\security;

use app\decibel\security\DDefaultSecurityPolicy;
use app\decibel\security\DSecurityPolicy;
use app\decibel\test\DTestCase;

/**
 * Test class for DDefaultSecurityPolicy.
 * Generated by Decibel on 2012-04-12 at 09:06:03.
 */
class DDefaultSecurityPolicyTest extends DTestCase
{
    public function setUp()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers app\decibel\security\DDefaultSecurityPolicy::__wakeup
     * @covers app\decibel\security\DDefaultSecurityPolicy::initialise
     */
    public function test__wakeup()
    {
        $policy = unserialize('O:43:"app\decibel\security\DDefaultSecurityPolicy":0:{}');
        $this->assertSame(DSecurityPolicy::AUTH_FACTORS_ONE, $policy->getFieldValue('authFactorRequirement'));
        $this->assertSame(10, $policy->getFieldValue('failedLoginLockout'));
        $this->assertNull($policy->getFieldValue('inactiveLockout'));
        $this->assertSame(10, $policy->getFieldValue('lockoutLength'));
        $this->assertNull($policy->getFieldValue('sessionLimit'));
        $this->assertSame(6, $policy->getFieldValue('minimumPasswordLength'));
        $this->assertSame(DSecurityPolicy::OVERRIDE_STRONGER, $policy->getFieldValue('overrideCondition'));
        $this->assertNull($policy->getFieldValue('passwordLife'));
        $this->assertSame(DSecurityPolicy::PASSWORD_STRENGTH_MEDIUM, $policy->getFieldValue('passwordStrength'));
        $this->assertNull($policy->getFieldValue('rememberedPasswords'));
        $this->assertSame(60, $policy->getFieldValue('sessionTimeout'));
    }

    /**
     * @covers app\decibel\security\DDefaultSecurityPolicy::__construct
     * @covers app\decibel\security\DDefaultSecurityPolicy::initialise
     */
    public function test__construct()
    {
        $policy = new DDefaultSecurityPolicy();
        $this->assertSame(DSecurityPolicy::AUTH_FACTORS_ONE, $policy->getFieldValue('authFactorRequirement'));
        $this->assertSame(10, $policy->getFieldValue('failedLoginLockout'));
        $this->assertNull($policy->getFieldValue('inactiveLockout'));
        $this->assertSame(10, $policy->getFieldValue('lockoutLength'));
        $this->assertNull($policy->getFieldValue('sessionLimit'));
        $this->assertSame(6, $policy->getFieldValue('minimumPasswordLength'));
        $this->assertSame(DSecurityPolicy::OVERRIDE_STRONGER, $policy->getFieldValue('overrideCondition'));
        $this->assertNull($policy->getFieldValue('passwordLife'));
        $this->assertSame(DSecurityPolicy::PASSWORD_STRENGTH_MEDIUM, $policy->getFieldValue('passwordStrength'));
        $this->assertNull($policy->getFieldValue('rememberedPasswords'));
        $this->assertSame(60, $policy->getFieldValue('sessionTimeout'));
    }

    /**
     * @covers app\decibel\security\DDefaultSecurityPolicy::__set
     * @expectedException app\decibel\debug\DReadOnlyParameterException
     */
    public function test__set()
    {
        $policy = new DDefaultSecurityPolicy();
        $policy->test = 'test';
    }

    /**
     * @covers app\decibel\security\DDefaultSecurityPolicy::getDescription
     */
    public function testgetDescription()
    {
        $policy = new DDefaultSecurityPolicy();
        $this->assertLabel($policy->getDescription(), 'description', 'app\\decibel\\security\\DDefaultSecurityPolicy');
    }

    /**
     * @covers app\decibel\security\DDefaultSecurityPolicy::getName
     */
    public function testgetName()
    {
        $policy = new DDefaultSecurityPolicy();
        $this->assertLabel($policy->getName(), 'name', 'app\\decibel\\security\\DDefaultSecurityPolicy');
    }

    /**
     * @covers app\decibel\security\DDefaultSecurityPolicy::isConfigurable
     */
    public function testisConfigurable()
    {
        $policy = new DDefaultSecurityPolicy();
        $this->assertFalse($policy->isConfigurable());
    }

    /**
     * @covers app\decibel\security\DDefaultSecurityPolicy::setFieldValue
     * @expectedException app\decibel\debug\DReadOnlyParameterException
     */
    public function testsetFieldValue()
    {
        $policy = new DDefaultSecurityPolicy();
        $policy->setFieldValue('test', 'test');
    }
}
