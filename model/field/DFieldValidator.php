<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DInvalidPropertyException;
use app\decibel\decorator\DRuntimeDecorator;
use app\decibel\utility\DDefinable;
use app\decibel\utility\DResult;
use app\decibel\validator\DValidator;

/**
 * Provides functionality to validate the value
 * of a {@link app::decibel::model::field::DField DField}.
 *
 * @author        Timothy de Paris
 */
class DFieldValidator extends DRuntimeDecorator
{
    /**
     * Checks whether required field values have been provided.
     *
     * @param    mixed $data The data requiring validation.
     *
     * @return    DResult
     */
    protected function checkRequiredValue($data)
    {
        $result = new DResult();
        // Check if value has been provided.
        if ($this->isRequired()
            && $this->getNullOption() === null
            && $this->isEmpty($data)
        ) {
            $result->setSuccess(false, '#fieldName# must be provided.');
        }

        return $result;
    }

    /**
     * Performs any specific validation of the provided data,
     * as required by this field.
     *
     * @param    mixed      $data      The data requiring validation.
     * @param    DDefinable $definable The object that requested validation.
     *
     * @return    DResult
     */
    protected function checkValidationRules($data, DDefinable $definable = null)
    {
        $result = new DResult();
        // Check each of the available validation rules.
        foreach ($this->getValidationRules() as $rule) {
            $validator = $rule[ DField::VALIDATION_RULE_TYPE ];
            $message = $rule[ DField::VALIDATION_RULE_MESSAGE ];
            $result->merge($this->checkValidationRule($validator, $data, $definable, $message));
        }

        return $result;
    }

    /**
     * Performs validation of the provided data against a {@link DValidator}.
     *
     * @param    DValidator $validator The validator.
     * @param    mixed      $data      The data requiring validation.
     * @param    DDefinable $definable The object that requested validation.
     * @param    string     $message   Message to use if validation fails.
     *
     * @return    DResult
     */
    protected function checkValidationRule(DValidator $validator, $data,
                                           DDefinable $definable = null, $message = null)
    {
        $validatorResult = $validator->validate(
            $data,
            $definable,
            $this->getDecorated()
        );
        $result = new DResult();
        if (sizeof($validatorResult) > 0) {
            if ($message) {
                $result->setSuccess(false, $message);
            } else {
                $result->setSuccess(false, $validatorResult);
            }
        }

        return $result;
    }

    /**
     * Performs any specific validation of the provided
     * data required by this field.
     *
     * @param    mixed      $data      The data requiring validation.
     * @param    DDefinable $definable The object that requested validation.
     *
     * @return    DResult
     */
    protected function checkValue($data, DDefinable $definable = null)
    {
        $result = new DResult;
        $result->merge(
            $this->checkRequiredValue($data)
        );
        $result->merge(
            $this->checkValidationRules($data, $definable)
        );

        return $result;
    }

    /**
     * Returns the qualified name of the class that can be decorated
     * by this decorator.
     *
     * @return    string
     */
    public static function getDecoratedClass()
    {
        return DField::class;
    }

    /**
     * Returns variables and values for use when replacing error messages
     * generated by the {@link DField::checkValue()} method.
     *
     * @param    array      $variables    Pointer in which an array of variable
     *                                    names will be returned.
     * @param    array      $values       Pointer in which an array of variable
     *                                    values will be returned.
     * @param    DDefinable $definable    The object that requested validation.
     *
     * @return    void
     */
    protected function getMessageVariables(array &$variables, array &$values,
                                           DDefinable $definable = null)
    {
        $variables = array(
            '#fieldName#',
            '#displayName#',
            '#displayNamePlural#',
        );
        $displayName = 'Data';
        $displayNamePlural = 'Data';
        if ($definable !== null) {
            try {
                $displayName = $definable->displayName;
                $displayNamePlural = $definable->displayNamePlural;
            } catch (DInvalidPropertyException $exception) {
            }
        }
        $values = array(
            $this->getDisplayName(),
            $displayName,
            $displayNamePlural,
        );
    }

    /**
     * Checks the supplied data against each of the registered
     * validation rules for this field.
     *
     * @param    mixed      $data      The data requiring validation.
     * @param    DDefinable $definable The object that requested validation.
     *
     * @return    DResult
     */
    public function validate($data, DDefinable $definable = null)
    {
        // Perform the actual validation.
        $result = $this->checkValue($data, $definable);
        // Replace variables in messages.
        if ($result->hasMessages()) {
            $messages = $result->getMessages();
            $variables = array();
            $values = array();
            $this->getMessageVariables($variables, $values, $definable);
            foreach ($messages as &$message) {
                $message = str_replace($variables, $values, $message);
            }
            $resultMessages = $result->getMessages();
            $resultMessages->clearMessages();
            $resultMessages->addMessages($messages, $this->getName());
        }

        return $result;
    }
}
