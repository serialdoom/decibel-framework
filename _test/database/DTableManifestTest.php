<?php
namespace tests\app\decibel\database;

use app\decibel\application\DAppInformation;
use app\decibel\database\DTableManifest;
use app\decibel\database\schema\DTableDefinition;
use app\decibel\Decibel;
use app\decibel\registry\DAppRegistry;
use app\decibel\stream\DFileStream;
use app\decibel\test\DTestCase;

/**
 * Test class for DTableManifest.
 */
class DTableManifestTest extends DTestCase
{
    /**
     * @covers app\decibel\database\DTableManifest::getTableDefinitions
     */
    public function testgetTableDefinitions()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
