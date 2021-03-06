<?php
namespace tests\app\decibel\regional;

use app\decibel\regional\DLabel;
use app\decibel\regional\DTranslationFile;
use app\decibel\stream\DTextStream;
use app\decibel\test\DTestCase;

class TestTranslationFile extends DTranslationFile
{
    public function testgetLabel($line, array &$labels)
    {
        return $this->getLabel($line, $labels);
    }
}

/**
 * Test class for DTranslationFile.
 * Generated by Decibel on 2012-04-12 at 09:10:05.
 */
class DTranslationFileTest extends DTestCase
{
    /**
     * Returns a string emmulating the content of a valid translation file.
     *
     * @return    string
     */
    public function getValidLabels()
    {
        return "level1\\level2-label1 = Label 1\n"
        . "level1-label2 = Label 2\n"
        . "level1-label3 = Label 3\n";
    }

    /**
     * @covers app\decibel\regional\DTranslationFile::__construct
     */
    public function test__construct()
    {
        $stream = new DTextStream($this->getValidLabels());
        $translationFile = new DTranslationFile($stream, 'en-gb');
        $this->assertInstanceOf(DTranslationFile::class, $translationFile);
    }

    /**
     * @covers app\decibel\regional\DTranslationFile::getLabel
     */
    public function testgetLabel_namespace()
    {
        $stream = new DTextStream($this->getValidLabels());
        $translationFile = new TestTranslationFile($stream, 'en-gb');
        $labels = array();
        $this->assertTrue($translationFile->testgetLabel("level1\\level2-label1 = Label 1\n", $labels));
        $this->assertSame(
            array(
                'level1\\level2' => array(
                    'label1' => 'Label 1',
                ),
            ),
            $labels
        );
    }

    /**
     * @covers app\decibel\regional\DTranslationFile::getLabel
     */
    public function testgetLabel_invalid()
    {
        $stream = new DTextStream($this->getValidLabels());
        $translationFile = new TestTranslationFile($stream, 'en-gb');
        $labels = array();
        $this->assertFalse($translationFile->testgetLabel("label5: Label 5\n", $labels));
        $this->assertSame(
            array(),
            $labels
        );
    }

    /**
     * @covers app\decibel\regional\DTranslationFile::getLabels
     */
    public function testgetLabels()
    {
        $stream = new DTextStream($this->getValidLabels());
        $translationFile = new TestTranslationFile($stream, 'en-gb');
        $this->assertSame(
            array(
                'level1\\level2' => array(
                    'label1' => 'Label 1',
                ),
                'level1'         => array(
                    'label2' => 'Label 2',
                    'label3' => 'Label 3',
                ),
            ),
            $translationFile->getLabels()
        );
    }
}
