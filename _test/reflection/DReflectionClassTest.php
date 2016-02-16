<?php
namespace tests\app\decibel\reflection;

use app\decibel\authorise\DGuestUser;
use app\decibel\model\DLightModel;
use app\decibel\model\DTranslatableModel;
use app\decibel\reflection\DReflectionClass;
use app\decibel\test\DTestCase;
use app\decibel\utility\DUtilityData;

class TestMissingClassConstant extends DLightModel
{
    public static function getDisplayName()
    {
        return 'Test Missing Class Constant';
    }

    public static function getDisplayNamePlural()
    {
        return 'Test Missing Class Constants';
    }

    protected function getStringValue()
    {
        return '';
    }
}

/**
 * Test class for DReflectionClass.
 * Generated by Decibel on 2012-04-12 at 09:07:35.
 */
class DReflectionClassTest extends DTestCase
{
    /**
     * @covers app\decibel\reflection\DReflectionClass::__construct
     * @covers app\decibel\reflection\DReflectionClass::getDescription
     * @covers app\decibel\reflection\DReflectionClass::getDisplayName
     */
    public function test__construct()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $reflection = new DReflectionClass(DGuestUser::class);
        $this->assertInstanceOf('app\\decibel\\reflection\\DReflectionClass', $reflection);
        $this->assertSame(null, $reflection->getDescription());
        $this->assertEqual(DGuestUser::getDisplayName(), $reflection->getDisplayName());
    }

    /**
     * @covers app\decibel\reflection\DReflectionClass::__construct
     * @covers app\decibel\reflection\DReflectionClass::getDescription
     * @covers app\decibel\reflection\DReflectionClass::getDisplayName
     */
    public function test__construct_abstract()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $reflection = new DReflectionClass(DTranslatableModel::class);
        $this->assertInstanceOf(DReflectionClass::class, $reflection);
        $this->assertSame(null, $reflection->getDescription());
        $this->assertSame(null, $reflection->getDisplayName());
    }

    //	/**
    //	 * @covers app\decibel\reflection\DReflectionClass::getFields
    //	 */
    //	public function testgetFields() {
    //		$reflection = new DReflectionClass('app\\decibel\\authorise\\DGuestUser');
    //		$guest = DGuestUser::create();
    //		//$this->assertSame($guest->getFields(), $reflection->getFields());
    //	}
    /**
     * @covers app\decibel\reflection\DReflectionClass::getInterfaceNames
     */
    public function testgetInterfaceNames()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $reflection = new DReflectionClass(DUtilityData::class);
        $this->assertSame(
            array(
                'ArrayAccess',
                'Iterator',
                'Traversable',
                'app\\decibel\\debug\\DDebuggable',
                'app\\decibel\\utility\\DDefinable',
            ),
            $reflection->getInterfaceNames()
        );
    }

    /**
     * @covers app\decibel\reflection\DReflectionClass::getInterfaceNames
     */
    public function testgetInterfaceNames_notInternal()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $reflection = new DReflectionClass(DUtilityData::class);
        $this->assertSame(
            array(
                'app\\decibel\\debug\\DDebuggable',
                'app\\decibel\\utility\\DDefinable',
            ),
            $reflection->getInterfaceNames(false)
        );
    }

    /**
     * @covers app\decibel\reflection\DReflectionClass::getQualifiedName
     */
    public function testgetQualifiedName()
    {
        $reflection = new DReflectionClass(DGuestUser::class);
        $this->assertSame(DGuestUser::class, $reflection->getQualifiedName());
    }
}