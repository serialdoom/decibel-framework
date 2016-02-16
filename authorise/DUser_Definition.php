<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\authorise;

use app\decibel\authorise\DUser;
use app\decibel\model\DModel;
use app\decibel\model\DModel_Definition;
use app\decibel\model\field\DDateTimeField;
use app\decibel\model\field\DEnumField;
use app\decibel\model\field\DEnumStringField;
use app\decibel\model\field\DIntegerField;
use app\decibel\model\field\DLinkedObjectField;
use app\decibel\model\field\DLinkedObjectsField;
use app\decibel\model\field\DTextField;
use app\decibel\model\index\DFulltextIndex;
use app\decibel\model\index\DUniqueIndex;
use app\decibel\regional\DLabel;
use app\decibel\regional\DLanguage;
use app\decibel\utility\DDate;
use app\decibel\validator\DEmailValidator;

/**
 * Decibel User Definition.
 *
 * @author        Timothy de Paris
 */
class DUser_Definition extends DModel_Definition
{
    /**
     * Creates a new DUser definition.
     *
     * @param    string $qualifiedName
     *
     * @return    static
     */
    public function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        $labelPersonalDetails = new DLabel(DUser::class, 'personalDetails');
        $labelRegionalDetails = new DLabel(DUser::class, 'regionalDetails');
        $labelSecurityDetails = new DLabel(DUser::class, 'securityDetails');
        // Set field information.
        $profile = new DLinkedObjectField(DUser::FIELD_PROFILE, 'Profile');
        $profile->setDescription('The user will inherit a number of properties from this profile. Please see the Profile documentation for information about these properties.');
        $profile->setLinkTo(DProfile::class);
        $profile->setAdditionalOption('userObject', $this->qualifiedName);
        $profile->setIntegrityMessage('One or more users are assigned to this profile.');
        $profile->setExportGroup('Account Details');
        $this->addField($profile);
        $groups = new DLinkedObjectsField(DUser::FIELD_GROUPS, 'Groups');
        $groups->setLinkTo(DGroup::class);
        $groups->setIntegrityMessage('One or more users are assigned to this group.');
        $this->addField($groups);
        $username = new DTextField(DUser::FIELD_USERNAME, new DLabel(DUser::class, DUser::FIELD_USERNAME));
        $username->setDescription('<p>The user will login using their Email Address.</p><p>This must be a unique value and a valid email address.</p>');
        $username->setMaxLength(255);
        $username->setRequired(true);
        $username->setExportGroup('Account Details');
        $this->addField($username);
        $salt = new DTextField('salt', 'Salt');
        $salt->setReadOnly(true);
        $salt->setNullOption('None');
        $salt->setDefault(null);
        $salt->setExportable(false);
        $salt->setMaxLength(128);
        $this->addField($salt);
        $password = new DTextField(DUser::FIELD_PASSWORD, 'Password');
        $password->setReadOnly(true);
        $password->setExportable(false);
        $password->setMaxLength(64);
        $this->addField($password);
        $title = new DTextField('title', 'Title');
        $title->setMaxLength(5);
        $title->setExportGroup($labelPersonalDetails);
        $this->addField($title);
        $email = new DTextField(DUser::FIELD_EMAIL, new DLabel(DUser::class, DUser::FIELD_EMAIL));
        $email->setMaxLength(255);
        $email->setRequired(true);
        $email->setExportGroup($labelPersonalDetails);
        $email->addValidationRule(new DEmailValidator());
        $this->addField($email);
        $firstName = new DTextField(DUser::FIELD_FIRST_NAME,
                                    new DLabel(DUser::class, DUser::FIELD_FIRST_NAME));
        $firstName->setMaxLength(50);
        $firstName->setRequired(true);
        $firstName->setExportGroup($labelPersonalDetails);
        $this->addField($firstName);
        $lastName = new DTextField(DUser::FIELD_LAST_NAME, new DLabel(DUser::class, DUser::FIELD_LAST_NAME));
        $lastName->setMaxLength(50);
        $lastName->setRequired(true);
        $lastName->setExportGroup($labelPersonalDetails);
        $this->addField($lastName);
        $timezone = new DEnumStringField(DUser::FIELD_TIMEZONE, 'Time Zone');
        $timezone->setDescription('<p>The time zone in which the user resides. Allows dates and times to be edited in the user\'s local time.</p>');
        $timezone->setNullOption('Default (' . DDate::getApplicationDefaultTimeZoneString() . ')');
        $timezone->setValues(DDate::getTimeZones());
        $timezone->setMaxLength(50);
        $timezone->setExportGroup($labelRegionalDetails);
        $this->addField($timezone);
        $language = new DEnumStringField('language', 'Language');
        $language->setDefault('getDefaultLanguage');
        $language->setDescription('<p>The language this user speaks.</p>');
        $language->setValues(DLanguage::getLanguageNames());
        $language->setMaxLength(5);
        $language->setRequired(true);
        $language->setExportGroup($labelRegionalDetails);
        $this->addField($language);
        $lockoutStatus = new DEnumField(DUser::FIELD_LOCKOUT_STATUS, 'Lockout Status');
        $lockoutStatus->setNullOption('Unlocked');
        $lockoutStatus->setValues(DUser::$lockoutOptions);
        $lockoutStatus->setDefault(null);
        $lockoutStatus->setSize(2);
        $lockoutStatus->setExportGroup($labelSecurityDetails);
        $this->addField($lockoutStatus);
        $lockoutExpiry = new DDateTimeField('lockoutExpiry', 'Lockout Expiry');
        $lockoutExpiry->setNullOption('Never');
        $lockoutExpiry->setExportGroup($labelSecurityDetails);
        $this->addField($lockoutExpiry);
        $lockoutReason = new DTextField('lockoutReason', 'Lockout Reason');
        $lockoutReason->setNullOption('None');
        $lockoutReason->setMaxLength(255);
        $lockoutReason->setExportGroup($labelSecurityDetails);
        $this->addField($lockoutReason);
        $loginCount = new DIntegerField('loginCount', 'Login Count');
        $loginCount->setUnsigned(true);
        $this->addField($loginCount);
        $lastLogin = new DDateTimeField('lastLogin', 'Last Login');
        $lastLogin->setNullOption('Never');
        $lastLogin->setDefault(null);
        $this->addField($lastLogin);
        // Register indexes.
        $uniqueUsername = new DUniqueIndex('unique_username');
        $uniqueUsername->addField($username);
        $this->addIndex($uniqueUsername);
        $indexName = new DFulltextIndex('search_name', 'Name');
        $indexName->addField($firstName);
        $indexName->addField($lastName);
        $this->addIndex($indexName);
        $indexUsername = new DFulltextIndex('search_username', 'Username');
        $indexUsername->addField($username);
        $this->addIndex($indexUsername);
        // Register event handlers.
        $this->setEventHandler(DModel::ON_LOAD, 'setInitialProfile');
    }

    /**
     * Returns the code of the default language for the object.
     *
     * @return    string
     */
    public static function getDefaultLanguage()
    {
        return DLanguage::getDefaultLanguageCode();
    }
}
