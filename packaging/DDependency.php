<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\packaging;

use app\decibel\configuration\DApplicationMode;
use app\decibel\utility\DDefinableObject;
use app\decibel\utility\DResult;
use DOMComment;
use DOMElement;
use DOMText;

/**
 * Describes a dependency of a %Decibel package.
 *
 * @section        why Why Would I Use It?
 *
 * A dependency is used to ensure that all requirements of a %Decibel App
 * (or the framework itself) are available in order for that App to run
 * correctly. Fully defining the dependencies of an App ensure that the
 * developer installing or upgrading an App does not cause unintended issues
 * simply due to the wrong version of PHP being installed on the system, or not
 * having a recent enough version of the %Decibel framework to support the
 * functionality of the App.
 *
 * @section        how How Do I Use It?
 *
 * Dependencies should be defined within the {@link app_manifests manifest}
 * for an App.
 *
 * %Decibel defines a range of dependency types that should cover most
 * requirements, however this class can be extended if a custom dependency
 * type is required.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        packaging
 */
abstract class DDependency
{
    use DDefinableObject;
    /**
     * Allows the current state to be overriden to test the dependency
     * in a particular situation.
     *
     * @var        mixed
     */
    protected $currentState;
    /**
     * The required value.
     *
     * @var        string
     */
    protected $required;
    /**
     * The recommended value.
     *
     * @var        string
     */
    protected $recommended;
    /**
     * The message that will be displayed if the pre-requisite is not met.
     *
     * If not provided, a default failure message will be generated dependent
     * on the pre-requisite type.
     *
     * @var        string
     */
    protected $message;
    /**
     * The Decibel application mode to which this pre-requisite applies.
     *
     * If null, the pre-requisite will apply to all modes.
     *
     * @var        string
     */
    protected $mode;

    /**
     * Creates a DDependency object from it's XML definition.
     *
     * @param    DOMElement $xml The XML definition.
     *
     * @return    DDependency
     */
    final public static function createFromXml(DOMElement $xml)
    {
        $type = $xml->getAttribute('type');
        $preRequisite = new $type();
        foreach ($xml->childNodes as $childNode) {
            /* @var $childNode DOMElement */
            if ($childNode instanceof DOMText
                || $childNode instanceof DOMComment
            ) {
                continue;
            }
            $field = $childNode->tagName;
            $value = self::parseXmlAttribute($childNode->textContent);
            $preRequisite->setFieldValue($field, $value);
        }

        return $preRequisite;
    }

    /**
     * Parses an XML attribute value.
     *
     * @param    mixed $value
     *
     * @return    mixed
     */
    protected static function parseXmlAttribute($value)
    {
        if ($value === 'true') {
            $value = true;
        } else {
            if ($value === 'false') {
                $value = false;
            } else {
                $value = trim($value);
            }
        }

        return $value;
    }

    /**
     * Returns a string describing this DDependency.
     *
     * @return    string
     */
    abstract public function __toString();

    /**
     * Determines if the current state of the pre-requisite meets the provided
     * criteria.
     *
     * @param    string $value The current state to compare.
     *
     * @return    bool
     */
    abstract protected function compareTo($value);

    /**
     * Returns the current state of this pre-requisite.
     *
     * @return    mixed
     */
    abstract public function getCurrentState();

    /**
     * Returns a message describing a pre-requisite failure.
     *
     * @return    string
     */
    abstract protected function getMessage();

    /**
     * Allows the current state to be overriden to test the dependency
     * in a particular situation.
     *
     * @param    mixed $currentState The overriden current state.
     *
     * @return    void
     */
    public function setCurrentState($currentState)
    {
        $this->currentState = $currentState;
    }

    /**
     * Tests the pre-requisite against the current Decibel installation.
     *
     * @return    DResult
     */
    public function test()
    {
        $result = new DResult();
        // Check if we need to test this.
        if ($this->mode === null
            || DApplicationMode::getMode() === $this->mode
        ) {
            $this->testRequired($result);
            $this->testRecommended($result);
        }

        return $result;
    }

    /**
     * Checks the recommended status of this dependency.
     *
     * @param    DResult $result
     *
     * @return    void
     */
    protected function testRecommended(DResult &$result)
    {
        if ($this->recommended !== null
            && !$this->compareTo($this->recommended)
        ) {
            $result->addMessage($this->getMessage());
        }
    }

    /**
     * Checks the required status of this dependency.
     *
     * @param    DResult $result
     *
     * @return    void
     */
    protected function testRequired(DResult &$result)
    {
        if ($this->required !== null
            && !$this->compareTo($this->required)
        ) {
            $result->setSuccess(false, $this->getMessage());
        }
    }
}
