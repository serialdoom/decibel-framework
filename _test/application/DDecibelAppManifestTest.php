<?php
namespace tests\app\decibel\application;

use app\decibel\application\DAppManifest;
use app\decibel\Decibel;
use PHPUnit_Framework_TestCase;

/**
 * Class DDecibelAppManifestTestCase
 *
 * Provides separation of concerns regarding test validations in the
 * AppManifestTest TestCases.
 *
 * @package tests\app\decibel\application
 */
class DDecibelAppManifestTestCase extends PHPUnit_Framework_TestCase
{
    /** @var DAppManifest */
    protected $decibelAppManifest = null;

    /**
     *
     */
    public function setUp()
    {
        $app = new Decibel();
        $this->decibelAppManifest = $app->setRelativePath('')->getManifest();
    }

    /**
     * @covers       app\decibel\application\DAppManifest::getAuthorName
     * @dataProvider addAppInformationProvider
     *
     * @param array $expectedManifestInformation
     */
    public function testAuthorName($expectedManifestInformation)
    {
        $this->assertSame($expectedManifestInformation['author'],
                          $this->decibelAppManifest->getAuthorName());
    }

    /**
     * @covers       app\decibel\application\DAppManifest::getName
     * @dataProvider addAppInformationProvider
     *
     * @param array $expectedManifestInformation
     */
    public function testName($expectedManifestInformation)
    {
        $this->assertSame($expectedManifestInformation['name'],
                          $this->decibelAppManifest->getName());
    }

    /**
     * @covers       app\decibel\application\DAppManifest::getCopyright
     * @dataProvider addAppInformationProvider
     *
     * @param array $expectedManifestInformation
     */
    public function testCopyright($expectedManifestInformation)
    {
        $this->assertSame($expectedManifestInformation['copyright'],
                          $this->decibelAppManifest->getCopyright());
    }

    /**
     * @covers       app\decibel\application\DAppManifest::getVersion
     * @dataProvider addAppInformationProvider
     *
     * @param array $expectedManifestInformation
     */
    public function testVersion($expectedManifestInformation)
    {
        $this->assertSame($expectedManifestInformation['version'],
                          $this->decibelAppManifest->getVersion());
    }

    /**
     * @covers       app\decibel\application\DAppManifest::getVersion
     * @dataProvider addAppInformationProvider
     *
     * @param array $expectedManifestInformation
     */
    public function testRepositoryUrl($expectedManifestInformation)
    {
        $this->assertSame($expectedManifestInformation['repositoryUrl'],
                          $this->decibelAppManifest->getRepositoryUrl());
    }

    /**
     * Centralised store to aggregate Decibel AppInformation to make
     * changes to the test more manageable during review.
     *
     * @return array
     */
    public function addAppInformationProvider()
    {
        return [ [
            [
                'name' => 'Decibel Framework',
                'author' => 'Decibel Technology',
                'version' => '6.8.0-dev',
                'copyright' => '2008-2016 Decibel Technology Limited',
                'repositoryUrl' => null,
                'updateMethod' => 'automatic',
            ]
        ] ];
    }
}
