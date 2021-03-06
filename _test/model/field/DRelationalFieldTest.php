<?php
namespace tests\app\decibel\model\field;

use app\decibel\authorise\DUser;
use app\decibel\model\DTranslatableModel;
use app\decibel\model\field\DRelationalField;
use app\decibel\test\DTestCase;

class TestRelationalField extends DRelationalField
{
    public function castValue($value)
    {
    }

    public function getDataType()
    {
    }

    public function getInternalDataType()
    {
    }

    public function getStandardDefaultValue()
    {
    }

    public function toString($data)
    {
    }
}

/**
 * Test class for DRelationalField.
 * Generated by Decibel on 2012-04-12 at 09:08:41.
 */
class DRelationalFieldTest extends DTestCase
{
    /**
     * @covers app\decibel\model\field\DRelationalField::__set
     */
    public function test__set()
    {
        $field = new TestRelationalField('test', 'Test');
        $field->relationalIntegrity = DRelationalField::RELATIONAL_INTEGRITY_NONE;
        $this->assertSame(DRelationalField::RELATIONAL_INTEGRITY_NONE, $field->relationalIntegrity);
    }

    /**
     * @covers app\decibel\model\field\DRelationalField::getLinkDisplayName
     */
    public function testgetLinkDisplayName()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $field = new TestRelationalField('test', 'Test');
        $field->setLinkTo(DTranslatableModel::class);
        $this->assertSame('link', $field->getLinkDisplayName());
        $field->setLinkTo(DUser::class);
        $this->assertEqual(DUser::getDisplayName(), $field->getLinkDisplayName());
    }

    /**
     * @covers app\decibel\model\field\DRelationalField::getLinkDisplayNamePlural
     */
    public function testgetLinkDisplayNamePlural()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $field = new TestRelationalField('test', 'Test');
        $field->setLinkTo(DTranslatableModel::class);
        $this->assertSame('links', $field->getLinkDisplayNamePlural());
        $field->setLinkTo(DUser::class);
        $this->assertEqual(DUser::getDisplayNamePlural(), $field->getLinkDisplayNamePlural());
    }

    /**
     * @covers app\decibel\model\field\DRelationalField::setDefaultOptions
     */
    public function testsetDefaultOptions()
    {
        $field = new TestRelationalField('test', 'Test');
        $this->assertSame(DRelationalField::RELATIONAL_INTEGRITY_REFERENTIAL, $field->relationalIntegrity);
        $this->assertNull($field->integrityMessage);
    }

    /**
     * @covers app\decibel\model\field\DRelationalField::setIntegrityMessage
     */
    public function testsetIntegrityMessage()
    {
        $field = new TestRelationalField('test', 'Test');
        $field->setIntegrityMessage('integrityMessage');
        $this->assertSame('integrityMessage', $field->integrityMessage);
    }
}
