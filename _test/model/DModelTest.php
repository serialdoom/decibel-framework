<?php
namespace tests\app\decibel\model;

use app\decibel\model\DModel;
use app\decibel\model\DModel_Definition;
use app\decibel\model\field\DIntegerField;
use app\decibel\model\field\DTextField;
use app\decibel\test\DTestCase;

class TestModel extends DModel
{
    public static function getDisplayName()
    {
        return 'Test Model';
    }

    public static function getDisplayNamePlural()
    {
        return 'Test Models';
    }

    protected function getStringValue()
    {
        return $this->getFieldValue('title');
    }

    public function dynamicGetter()
    {
        return $this->getFieldValue('title');
    }

    public function dynamicSetter($value)
    {
        $this->setFieldValue('title', $value);
    }

    public function &_getData()
    {
        return $this->fieldValues;
    }

    public function &_getFieldPointers()
    {
        return $this->fieldPointers;
    }
}

class TestModel_Definition extends DModel_Definition
{
    public function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        $title = new DTextField('title', 'Title');
        $title->setMaxLength(255);
        $this->addField($title);
        $count = new DIntegerField('count', 'Count');
        $this->addField($count);
    }
}
