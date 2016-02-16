<?php
namespace tests\app\decibel\regional;

use app\decibel\application\DApp;
use app\decibel\cache\DPublicCache;
use app\decibel\configuration\DApplicationMode;
use app\decibel\regional\DLabel;
use app\decibel\test\DTestCase;

/**
 * Wrapper so we can test protected method with reference variable
 */
class TestDLabel extends DLabel
{
    public static function unprotectedMergeLabels(&$labels, $newLabels)
    {
        self::mergeLabels($labels, $newLabels);
    }

    public static function unprotectedFormatNumericVariables(&$variables, $languageCode)
    {
        self::formatNumericVariables($variables, $languageCode);
    }
}

/**
 * Test class for DLabel.
 * Generated by Decibel on 2011-10-31 at 14:12:23.
 */
class DLabelTest extends DTestCase
{
    /**
     * @covers    app\decibel\regional\DLabel::__construct
     * @covers    app\decibel\regional\DLabel::getVariables
     * @covers    app\decibel\regional\DLabel::getName
     * @covers    app\decibel\regional\DLabel::getNamespace
     */
    public function test__construct_validLabel()
    {
        $vars = array(
            'name' => 'test',
            'date' => 'today',
        );
        $label = new DLabel('app\\decibel', 'yes', $vars);
        $this->assertSame(
            $label->getVariables(), $vars
        );
        $this->assertSame(
            $label->getName(), 'yes'
        );
        $this->assertSame(
            $label->getNamespace(), 'app\\decibel'
        );
    }

    /**
     * @covers                app\decibel\regional\DLabel::__construct
     * @covers                app\decibel\regional\DLabel::getVariables
     * @covers                app\decibel\regional\DLabel::getName
     * @covers                app\decibel\regional\DLabel::getNamespace
     * @expectedException    app\decibel\regional\DUnknownLabelException
     */
    public function test__construct_invalidLabel()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $vars = array(
            'name' => 'test',
            'date' => 'today',
        );
        new DLabel('invalid\\label', 'test', $vars);
    }

    /**
     * @covers    app\decibel\regional\DLabel::__construct
     * @covers    app\decibel\regional\DLabel::getName
     * @covers    app\decibel\regional\DLabel::getNamespace
     */
    public function test__construct_placeholder()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $identifier = 'placeholder';
        $label = new DLabel($identifier);
        $this->assertSame(
            $label->getName(), null
        );
        $this->assertSame(
            $label->getNamespace(), null
        );
    }

    /**
     * @covers  app\decibel\regional\DLabel::__toString
     */
    public function test__toString_validLabel()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $label = new DLabel('app\\decibel', 'yes');
        $this->assertSame(
            'Yes',
            $label->__toString()
        );
    }

    /**
     * @covers    app\decibel\regional\DLabel::loadLabelsForLanguage
     */
    public function xtestloadLabelsForLanguage()
    {
        // Test the clear cache case
        $cache = DPublicCache::load();
        $cache->clear();
        $this->assertSame(
            $this->executeMethod(DLabel::class, 'loadLabelsForLanguage', array('en-gb')),
            $this->executeMethod(DLabel::class, 'loadLanguageFiles', array('en-gb'))
        );
        // Labels will now be in the cache so test again
        $this->assertSame(
            $this->executeMethod(DLabel::class, 'loadLabelsForLanguage', array('en-gb')),
            $this->executeMethod(DLabel::class, 'loadLanguageFiles', array('en-gb'))
        );
    }

    /**
     * @covers    app\decibel\regional\DLabel::loadLabelsForLanguage
     */
    public function xtestloadLabelsForLanguage_invalid()
    {
        // Test the clear cache case
        $cache = DPublicCache::load();
        $cache->clear();
        $this->assertSame(
            array('APP' => array()),
            $this->executeMethod(DLabel::class, 'loadLabelsForLanguage', array('invalid'))
        );
    }

    /**
     * @covers                app\decibel\regional\DLabel::getRuleNumberFor
     * @expectedException    app\decibel\regional\language\DMissingLanguageDefinitionException
     */
    public function xtestgetRuleNumberFor_invalid_debugMode()
    {
        $this->executeMethod(DLabel::class, 'getRuleNumberFor', array('invalid', 1));
    }

    /**
     * @covers app\decibel\regional\DLabel::getRuleNumberFor
     */
    public function xtestgetRuleNumberFor_invalid_productionMode()
    {
        $this->setApplicationMode(DApplicationMode::MODE_PRODUCTION);
        $this->assertSame(
            0, $this->executeMethod(DLabel::class, 'getRuleNumberFor', array('invalid', 1))
        );
    }

    /**
     * @covers app\decibel\regional\DLabel::mergeLabels
     */
    public function xtestmergeLabels()
    {
        $labels = array('app\\decibel' => array('yes' => 'Yes'));
        $newLabels = array('app\\decibel' => array('no' => 'No'));
        TestDLabel::unprotectedMergeLabels($labels, $newLabels);
        $this->assertSame(
            array(
                'yes' => 'Yes',
                'no'  => 'No',
            ), $labels['app\\decibel']
        );
    }

    /**
     * @covers app\decibel\regional\DLabel::mergeLabels
     */
    public function xtestmergeLabels_newNamespace()
    {
        $labels = array('app\\decibel' => array('yes' => 'Yes'));
        $newLabels = array(DApp::class => array('app' => 'App'));
        TestDLabel::unprotectedMergeLabels($labels, $newLabels);
        $this->assertSame(
            array(
                'app\\decibel'       => array('yes' => 'Yes'),
                DApp::class => array('app' => 'App'),
            ),
            $labels
        );
    }

    /**
     * @covers app\decibel\regional\DLabel::formatNumericVariables
     */
    public function xtestformatNumericVariables()
    {
        $variables = array(123.45, 678.91);
        $languageCode = 'fr';
        TestDLabel::unprotectedFormatNumericVariables($variables, $languageCode);
        $this->assertSame(
            array('123,45', '678,91'),
            $variables
        );
    }

    /**
     * @covers app\decibel\regional\DLabel::define
     */
    public function xtestdefine()
    {
        $label = new DLabel('app\\decibel', 'yes');
        $this->assertSame(
            null,
            $this->executeMethod($label, 'define')
        );
    }
}
