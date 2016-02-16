<?php

//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//

namespace app\decibel;

use app\decibel\authorise\DPrivilege;
use app\decibel\authorise\DUser;
use app\decibel\authorise\DUserCapabilityCode;
use app\decibel\database\DStoredProcedure;
use app\decibel\database\maintenance\DOptimiseDatabase;
use app\decibel\model\DChild;
use app\decibel\model\DModel;
use app\decibel\model\DModelActionRecord;
use app\decibel\model\utilisation\DUtilisationRecord;
use app\decibel\regional\DLanguage;
use app\decibel\router\DRouter;

/******************************************************************************/
/* Queries																	  */
/******************************************************************************/

DStoredProcedure::register('app\\decibel\\application\\DConfigurationManager-updateClassOption',
                           "REPLACE INTO `decibel_application_dconfigurationmanager` SET `qualifiedName`='#qualifiedName#', `name`='#name#', `value`='#value#'");
DStoredProcedure::register('app\\decibel\\application\\DConfigurationManager-updateGlobalOption',
                           "REPLACE INTO `decibel_application_dconfigurationmanager` SET `name`='#name#', `value`='#value#'");
DStoredProcedure::register('app\\decibel\\model\\DAuditRecord-purgeRecords',
                           "DELETE FROM `#table#` WHERE `created`<#time#");
DStoredProcedure::register('app\\decibel\\model\\DBaseModel-delete',
                           "DELETE FROM `#tableName#` WHERE `#tableName#`.`id`=#id# LIMIT 1");
DStoredProcedure::register('app\\decibel\\model\\DBaseModel-load',
                           "SELECT * FROM `#tableName#` WHERE `#tableName#`.`id`=#id# LIMIT 1");
DStoredProcedure::register('app\\decibel\\model\\DChild-getOrphans',
                           "SELECT `decibel_model_dchild`.`id` FROM `decibel_model_dchild` LEFT JOIN `decibel_model_dmodel` ON (`decibel_model_dchild`.`parent`=`decibel_model_dmodel`.`id`) WHERE `decibel_model_dchild`.`parent`=0 OR `decibel_model_dmodel`.`id` IS NULL");
DStoredProcedure::register('app\\decibel\\model\\DModel-cleanInvalidModels',
                           "DELETE FROM `decibel_model_dmodel` WHERE `qualifiedName` NOT IN (#models#)");
DStoredProcedure::register('app\\decibel\\model\\DModel-cleanMissingModels',
                           "DELETE `#table#` FROM `#table#` LEFT JOIN `decibel_model_dmodel` USING (`id`) WHERE `decibel_model_dmodel`.`id` IS NULL");
DStoredProcedure::register('app\\decibel\\model\\DModel-getIdsForQualifiedName',
                           "SELECT `qualifiedName`, `id` FROM `decibel_model_dmodel` WHERE `qualifiedName` IN (#qualifiedNames#)");
DStoredProcedure::register('app\\decibel\\model\\DModel-getQualifiedNameForId',
                           "SELECT `qualifiedName` FROM `decibel_model_dmodel` WHERE `id`=#id# LIMIT 1");
DStoredProcedure::register('app\\decibel\\model\\utilisation\\DUtilisationRecord-clean',
                           "DELETE `decibel_index_utilisation` FROM `decibel_index_utilisation` LEFT JOIN `decibel_model_dmodel` AS `to` ON (`to`.`id`=`decibel_index_utilisation`.`to`) LEFT JOIN `decibel_model_dmodel` AS `from` ON (`from`.`id`=`decibel_index_utilisation`.`from`) WHERE ISNULL(`to`.`id`) OR ISNULL(`from`.`id`)");
DStoredProcedure::register('app\\decibel\\model\\field\\DArrayField-cleanDatabase',
                           "DELETE `decibel_model_field_darrayfield` FROM `decibel_model_field_darrayfield` LEFT JOIN `decibel_model_dmodel` AS `parent` ON (`parent`.`id`=`decibel_model_field_darrayfield`.`id`) WHERE ISNULL(`parent`.`id`)");
DStoredProcedure::register('app\\decibel\\model\\field\\DArrayField-delete',
                           "DELETE FROM `decibel_model_field_darrayfield` WHERE `id`=#id# AND `field`='#fieldName#'");
DStoredProcedure::register('app\\decibel\\model\\field\\DArrayField-load',
                           "SELECT `key`, `value` FROM `decibel_model_field_darrayfield` WHERE `id`=#id# AND `field`='#fieldName#'");
DStoredProcedure::register('app\\decibel\\model\\field\\DArrayField-save',
                           "INSERT INTO `decibel_model_field_darrayfield` SET `id`=#id#, `field`='#fieldName#', `key`='#key#', `value`='#value#'");
DStoredProcedure::register('app\\decibel\\model\\field\\DLinkedObjectsField-cleanDatabase',
                           "DELETE `decibel_model_field_dlinkedobjectsfield` FROM `decibel_model_field_dlinkedobjectsfield` LEFT JOIN `decibel_model_dmodel` AS `from` ON (`from`.`id`=`decibel_model_field_dlinkedobjectsfield`.`from`) WHERE ISNULL(`from`.`id`)");
DStoredProcedure::register('app\\decibel\\model\\field\\DLinkedObjectsField-delete',
                           "DELETE FROM `decibel_model_field_dlinkedobjectsfield` WHERE `from`=#from# AND `field`='#fieldName#'");
DStoredProcedure::register('app\\decibel\\model\\field\\DLinkedObjectsField-save',
                           "REPLACE INTO `decibel_model_field_dlinkedobjectsfield` SET `from`=#from#, `field`='#fieldName#', `to`=#to#, `position`=#position#");
DStoredProcedure::register('app\\decibel\\model\\field\\DLinkedObjectsField-load',
                           "SELECT `to` FROM `decibel_model_field_dlinkedobjectsfield` WHERE `from`=#from# AND `field`='#fieldName#' ORDER BY `position`");
DStoredProcedure::register('app\\decibel\\task\\DQueue-cancelTask',
                           "DELETE FROM `decibel_task_dqueue` WHERE `guid`='#guid#' LIMIT 1");
DStoredProcedure::register('app\\decibel\\task\\DQueue-checkTaskStatus',
                           "SELECT `queue`, `task`, `queued`, `started`, `progress`, `processId` FROM `decibel_task_dqueue` WHERE `guid`='#guid#' LIMIT 1");
DStoredProcedure::register('app\\decibel\\task\\DQueue-getQueue',
                           "SELECT `guid`, `queue`, `task`, `queued`, `owner` FROM `decibel_task_dqueue` ORDER BY `queue`, `queued`");
DStoredProcedure::register('app\\decibel\\task\\DQueue-enqueueTask',
                           "INSERT INTO `decibel_task_dqueue` SET `queue`='#queue#', `task`='#task#', `queued`=UNIX_TIMESTAMP(), `owner`=#owner#");
DStoredProcedure::register('App_LinkedObject_deleteTo',
                           "DELETE FROM `decibel_model_field_dlinkedobjectsfield` WHERE `to`=#to#");
// Template stored procedures.
DStoredProcedure::register('App_ModelObject_firstSave', "INSERT INTO `%s` SET `id`=LAST_INSERT_ID(), %s");
DStoredProcedure::register('App_ModelObject_firstSave_modelobject',
                           array("INSERT INTO `%s` SET %s", "UPDATE `decibel_model_dmodel` SET `guid`=MD5(CONCAT('#qualifiedName#', LAST_INSERT_ID())) WHERE `id`=LAST_INSERT_ID()"));
DStoredProcedure::register('App_ModelObject_save', "REPLACE INTO `%s` SET `id`=#id#, %s");
DStoredProcedure::register('App_ModelObject_save_modelobject', "UPDATE `%s` SET %s WHERE `id`=#id#");

/******************************************************************************/
/* Event Handlers															  */
/******************************************************************************/

DOptimiseDatabase::subscribeObserver(array(DModel::class, 'cleanDatabase'));
DOptimiseDatabase::subscribeObserver(array(DChild::class, 'cleanDatabase'));
DModel::subscribeObserver(array(DModelActionRecord::class, 'logModelAction'), DModel::ON_FIRST_SAVE);
DModel::subscribeObserver(array(DModelActionRecord::class, 'logModelAction'), DModel::ON_SUBSEQUENT_SAVE);
DModel::subscribeObserver(array(DModelActionRecord::class, 'logModelAction'), DModel::ON_DELETE);
DModel::subscribeObserver(array(DUtilisationRecord::class, 'index'), DModel::ON_SAVE);
DModel::subscribeObserver(array(DUtilisationRecord::class, 'deIndex'), DModel::ON_DELETE);
DUser::subscribeObserver(array(DUserCapabilityCode::class, 'clearCapabilityCache'), DUser::ON_UNCACHE);
// Show the debug console for redirectable HTTP responses.
DRouter::subscribeObserver(array('app\\decibel\\http\\DHttpResponse', 'showDebugConsole'), DRouter::ON_HTTP_RESPONSE);

/******************************************************************************/
/* Privileges																  */
/******************************************************************************/

DPrivilege::registerPrivilege('app\\decibel\\configuration-Auditing', 'Auditing Configuration',
                              'Manage retention periods and other settings for audit records.');
DPrivilege::registerPrivilege('app\\decibel\\configuration-General', 'Application Configuration',
                              'Manage overall application and App configuration settings.');
DPrivilege::registerPrivilege('app\\decibel\\configuration-Security', 'Application Configuration',
                              'Manage security policies and configuration settings.');
DPrivilege::registerPrivilege('app\\decibel\\maintenance-Cache', 'Application Management',
                              'Clear the application cache.');
DPrivilege::registerPrivilege('app\\decibel\\maintenance-General', 'Application Management',
                              'Perform maintenance functions such as optimising the database.');
DPrivilege::registerPrivilege('app\\decibel\\maintenance-Update', 'Application Management',
                              'Upload and apply updates to the application, website themes and Apps.');
DPrivilege::registerPrivilege('app\\decibel\\model\\DBaseModel-Clone', 'Content',
                              'Create new pages and other content by copying existing content.');
DPrivilege::registerPrivilege('app\\decibel\\security\\DIpAddress-Edit', 'Security',
                              'Manage known, trusted and blocked IP addresses.');

/******************************************************************************/
/* Languages																  */
/******************************************************************************/

DLanguage::registerLanguage('en-gb', 'English (Great Britain)', 'English', 'gb', DLanguage::DIRECTION_LTR);
