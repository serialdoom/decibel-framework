<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\auditing;

use app\decibel\application\DClassManager;
use app\decibel\configuration\DApplicationMode;
use app\decibel\task\DNightlyTask;

/**
 * Removes expired audit records according to their retention settings.
 *
 * @author        Timothy de Paris
 */
class DCleanAuditRecords extends DNightlyTask
{
    /**
     * Executes the task.
     *
     * This function will be called by the task scheduler whenever the
     * task is scheduled to run.
     *
     * @return    void
     */
    protected function execute()
    {
        // Determines available audit records.
        $auditRecords = DClassManager::getClasses(DAuditRecord::class);
        $totalAuditRecords = count($auditRecords);
        foreach ($auditRecords as $currentRecord => $qualifiedName) {
            // Load the audit record and purge.
            $definition = $qualifiedName::getDefinition();
            $definition->purge();
            // Update task progress.
            $this->updateProgress($currentRecord, $totalAuditRecords);
        }
    }
}
