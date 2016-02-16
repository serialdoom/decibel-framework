<?php
namespace tests\app\decibel\regional;

use app\decibel\application\DAppInformation;
use app\decibel\regional\DTranslationFileInformation;
use app\decibel\registry\DRegistry;
use app\decibel\test\DTestCase;
use ReflectionClass;

/**
 * Test class for DTranslationFileInformation.
 */
class DTranslationFileInformationTest extends DTestCase
{
    /**
     * The mock registry used to construct any registry hives.
     *
     * @var        DRegistry
     */
    protected $registry;
    /**
     * A DTranslationFileInformation instance that can be tested.
     *
     * @var        DTranslationFileInformation
     */
    protected $translationFileInformation;

    /**
     * This method is run once for each test method (and on fresh instances)
     * of the test case class.
     */
    public function setUp()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        // Create mock for abstract DAppRegistry class.
        // 1 - Set $callOriginalConstructor = false (4th argument) so that mock
        //		can be created.
        // 2 - Set $mockedMethods = array('getHive') as this is a concrete
        //		method that will be mocked.
        $this->registry = $this->getMockForAbstractClass('app\\decibel\\registry\\DAppRegistry', array(), '', false,
                                                         true, true, array('getHive'));
        // Create DAppInformation mock.
        // 1 - Set pass constructor arguments in the 3rd argument of the getMock
        //		method.
        $appInformation = $this->getMock('app\\decibel\\application\\DAppInformation', array(), array($this->registry));
        // Set the DAppInformation::getApps method to return an empty array
        // rather than the default of <code>null</code>, to stop errors resulting
        // from foreach loop over the DAppInformation::getApps method value.
        $appInformation->expects($this->any())
                       ->method('getApps')
                       ->will($this->returnValue(array()));
        // Set the DRegistry::getHive method to return the mock DAppInformation
        // object that was created above
        $this->registry->expects($this->any())
                       ->method('getHive')
                       ->will($this->returnValue($appInformation));
        // Create the DTranslationFileInformation object with the mock DRegistry
        $this->translationFileInformation = new DTranslationFileInformation($this->registry);
    }

    /**
     * @covers app\decibel\regional\DTranslationFileInformation::__sleep
     */
    public function test__sleep()
    {
        $sleep = $this->translationFileInformation->__sleep();
        $this->assertContains('translationFiles', $sleep);
    }

    /**
     * @covers app\decibel\regional\DTranslationFileInformation::generateDebug
     */
    public function testgenerateDebug()
    {
        $debug = $this->translationFileInformation->generateDebug();
        $this->assertArrayHasKey('translationFiles', $debug);
    }

    /**
     * @todo    implement testgenerateChecksum.
     */
    public function testgenerateChecksum()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers app\decibel\regional\DTranslationFileInformation::getDependencies
     */
    public function testgetDependencies()
    {
        $this->assertSame(
            array(DAppInformation::class),
            $this->translationFileInformation->getDependencies()
        );
    }

    /**
     * @covers app\decibel\regional\DTranslationFileInformation::getFormatVersion
     */
    public function testgetFormatVersion()
    {
        $this->assertSame(
            1,
            $this->translationFileInformation->getFormatVersion()
        );
    }

    /**
     * @covers app\decibel\regional\DTranslationFileInformation::getTranslationFiles
     */
    public function testgetTranslationFiles()
    {
        $this->assertContainsOnlyInstancesOf(
            'app\\decibel\\regional\\DTranslationFile',
            $this->translationFileInformation->getTranslationFiles('en-gb')
        );
    }

    /**
     * @covers    app\decibel\regional\DTranslationFileInformation::merge
     */
    public function testmerge_noMerge()
    {
        // Create a DLanguageInformation mock, that will be attempted to be
        // merged into the $translationInformation.
        // 1 - Set pass constructor arguments in the 3rd argument of the getMock
        //		method.
        $languageInformation = $this->getMock('app\\decibel\\regional\\language\\DLanguageInformation', array(),
                                              array($this->registry));
        $this->assertSame(
            false,
            $this->translationFileInformation->merge($languageInformation)
        );
    }

    /**
     * @covers    app\decibel\regional\DTranslationFileInformation::merge
     */
    public function testmerge_validMerge()
    {
        $translationFileInformation2 = new DTranslationFileInformation($this->registry);
        // Create a reflection class to be able to access the protected
        // DTranslationFileInformation::translationFiles property, to test that
        // the merge was successful.
        $reflection = new ReflectionClass(DTranslationFileInformation::class);
        $property = $reflection->getProperty('translationFiles');
        // Set the property accessible
        $property->setAccessible(true);
        // Get the value of the property before the merge
        $translationFilesBeforeMerge = $property->getValue($this->translationFileInformation);
        $translationFilesMerged = $property->getValue($translationFileInformation2);
        $this->assertTrue($this->translationFileInformation->merge($translationFileInformation2));
        // Get the value of the property after the merge
        $translationFilesAfterMerge = $property->getValue($this->translationFileInformation);
        // Test that the merge was successful
        $this->assertSame(
            array_merge_recursive($translationFilesBeforeMerge, $translationFilesMerged),
            $translationFilesAfterMerge
        );
    }
}
