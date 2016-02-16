<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel;

use app\decibel\application\DAppManager;
use app\decibel\authorise\DPrivilege;
use app\decibel\model\field\DBooleanField;
use app\decibel\utility\DDate;

// Load a list of hasing functions
// for DECIBEL_CORE_SESSIONHASH configuration.
if (extension_loaded('hash')) {
    $defaultHash = 'md5';
    $hashFunctions = array(
        'md5'    => 'MD5',
        'sha1'   => 'SHA-1',
        'sha256' => 'SHA-256',
        'sha512' => 'SHA-512',
    );
} else {
    $defaultHash = '0';
    $hashFunctions = array(
        '0' => 'MD5',
        '1' => 'SHA-1',
    );
}
$checkBlockedIps = new DBooleanField('DECIBEL_CORE_BLOCKIPS', 'Check Blocked IPs');
$checkBlockedIps->setDescription('<p>If enabled, Decibel will block access from IP addresses marked as blocked in the IP Addresses list. This will have a small impact on performance and should therefore only be enabled if neccessary.</p>');
$checkBlockedIps->setDefault(false);
DAppManager::registerConfigurationOption(DAppManager::CONFIG_OPTION_SECURITY, 'Authorisation', $checkBlockedIps,
                                         'app\\decibel\\configuration-Security');
DAppManager::registerConfigurationOption('DECIBEL_CORE_EXPIRYTOLERANCE', DAppManager::CONFIG_OPTION_SECURITY, 'Session',
                                         'Expiry Tolerance',
                                         '<p>The number of seconds of tolerance for session expiry checks. Within this period of time, accounts that have reached their session expiry time due to inactivity may still be considered active.</p><p>A higher tolerance will reduce load on busy websites with a large number of user accounts.</p>',
                                         60, 'app\\decibel\\widget\\DEnumWidget',
                                         array('size' => 2, 'values' => array(30 => '30 seconds', 60 => '1 minute', 300 => '5 minutes', 3000 => '10 minutes')),
                                         DPrivilege::ROOT);
DAppManager::registerConfigurationOption('DECIBEL_CORE_SESSIONHASH', DAppManager::CONFIG_OPTION_SECURITY, 'Session',
                                         'Hash Function',
                                         '<p>The hashing function that will be used to generate session IDs. Hashing functions are ordered by strength (weakest to strongest), with stronger hashing functions generating more secure session IDs, consequently consuming more resource.</p>',
                                         $defaultHash, 'app\\decibel\\widget\\DEnumStringWidget',
                                         array('values' => $hashFunctions), DPrivilege::ROOT);
DAppManager::registerConfigurationOption('DECIBEL_CORE_SESSIONDOMAIN', DAppManager::CONFIG_OPTION_SECURITY, 'Session',
                                         'Cookie Domain',
                                         '<p>The domain on which the session cookie will be set.</p><p><strong>Note</strong>: The session cookie domain must be prefixed by a dot separator, for example <code>.mydomain.com</code></p>Setting this to a higher level domain will allow logins to occur across multiple domains, for example a setting of <code>.mydomain.com</code> would allow logins to be shared across <code>www.mydomain.com</code> and <code>login.mydomain.com</code></p><p>If left blank, this will be automatically determined by Decibel.</p>',
                                         '', 'app\\decibel\\widget\\DTextWidget', array(), DPrivilege::ROOT);
DAppManager::registerConfigurationOption('DECIBEL_CORE_RPCPATH', DAppManager::CONFIG_OPTION_ROUTER, 'Paths', 'RPC Path',
                                         '<p>The path under the website root at which remote procedure calls can be made.</p><p>For example, an RPC path of <code>remote/</code> would allow execution of the following remote procedure: <code>http://www.website.com/remote/decibel/configuration/DSelectApplicationMode?mode=production</code><p><strong>Note</strong>: This must end with a forward slash (/)</p>',
                                         'remote/', 'app\\decibel\\widget\\DTextWidget', array(), DPrivilege::ROOT);
DAppManager::registerConfigurationOption('DECIBEL_REGIONAL_DEFAULTLANGUAGE', DAppManager::CONFIG_OPTION_GENERAL,
                                         'Regional', 'Default Language',
                                         '<p>The default language for the application.</p>', 'en-gb',
                                         'app\\decibel\\widget\\DTextWidget', array(), DPrivilege::ROOT);
DAppManager::registerConfigurationOption('DECIBEL_REGIONAL_TIMEZONE', DAppManager::CONFIG_OPTION_GENERAL, 'Regional',
                                         'Time Zone',
                                         '<p>The default time zone for the application. All dates and times will be represented in local time for this time zone.</p>',
                                         '', 'app\\decibel\\widget\\DEnumStringWidget',
                                         array('values' => DDate::getTimeZones(), 'nullOption' => 'Server Default (' . DDate::getServerDefaultTimeZoneString() . ')'));
DAppManager::registerConfigurationOption('DECIBEL_REGIONAL_DATEFORMAT', DAppManager::CONFIG_OPTION_GENERAL, 'Regional',
                                         'Date Format', '<p>The format in which dates will be shown.</p>', 'dd/mm/yy',
                                         'app\\decibel\\widget\\DEnumStringWidget',
                                         array('values' => array('dd/mm/yy' => 'dd/mm/yyyy', 'mm/dd/yy' => 'mm/dd/yyyy', 'yy/mm/dd' => 'yyyy/mm/dd')));
DAppManager::registerConfigurationOption('DECIBEL_UPDATE_BLOCKWAITTIME', DAppManager::CONFIG_OPTION_UPDATE,
                                         'Update Queueing', 'Maximum Wait Time',
                                         '<p>Requests to this server will be automatically queued during an update to ensure the integrity of content delivered to website visitors. This option specifies the maximum number of seconds that each request will be queued before being shown the Update Message.</p>',
                                         10, 'app\\decibel\\widget\\DIntegerWidget', array(), DPrivilege::ROOT);
DAppManager::registerConfigurationOption('DECIBEL_UPDATE_BLOCKMESSAGE', DAppManager::CONFIG_OPTION_UPDATE,
                                         'Update Queueing', 'Update Message',
                                         '<p>The message that will be shown to visitors arriving during an update, if the update takes longer than the configured  Maximum Wait Time.</p><p>This message can contain HTML, however should not rely on any linked stylesheets, JavaScript or images served by Decibel as these will be unavailable during the update process.</p>',
                                         '<p>This website is currently undergoing maintenance. Please try again in 5 minutes.</p>',
                                         'app\\decibel\\widget\\DTextWidget', array(), DPrivilege::ROOT);
