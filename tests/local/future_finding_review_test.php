<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_upgradeassistant\local;

/**
 * A documented future-release review must survive report reconciliation.
 *
 * @package    local_upgradeassistant
 * @category   test
 * @covers     \local_upgradeassistant\local\report_builder
 */
final class future_finding_review_test extends \advanced_testcase {
    /**
     * The recorded administrator decision is not overwritten when reopening the report.
     *
     * @return void
     */
    public function test_future_release_review_survives_lifecycle_synchronisation(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('lifecycledata', json_encode(['versions' => [
            ['branch' => '502', 'version' => '5.2', 'label' => 'Moodle 5.2',
                'releasedate' => '2026-04-20', 'generalend' => '2030-01-01',
                'securityend' => '2031-01-01'],
            ['branch' => '503', 'version' => '5.3', 'label' => 'Moodle 5.3',
                'releasedate' => '2099-01-01', 'generalend' => '2100-01-01',
                'securityend' => '2101-01-01'],
        ]]), 'local_upgradeassistant');
        $reportid = $DB->insert_record('local_ua_reports', (object)[
            'uuid' => '8c229611-4e30-4444-8888-141917f11111',
            'userid' => $USER->id,
            'currentbranch' => '502',
            'targetbranch' => '503',
            'riskscore' => 22,
            'risklevel' => 'medium',
            'status' => 'generated',
            'summary' => '{}',
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
        $findingid = $DB->insert_record('local_ua_items', (object)[
            'reportid' => $reportid,
            'category' => 'lifecycle',
            'code' => 'lifecycle_target_future',
            'title' => 'Future target',
            'description' => 'Future Moodle release',
            'severity' => 'high',
            'status' => 'open',
            'recommendation' => 'Review the release',
            'evidence' => '{}',
            'sortorder' => 10,
            'timecreated' => time(),
        ]);

        $codes = array_column(lifecycle_manager::findings('502', '503'), 'code');
        $this->assertContains('lifecycle_target_future', $codes);

        report_builder::review_plugin_finding((int)$reportid, (int)$findingid, 'Staging only, approved by admin');
        $report = $DB->get_record('local_ua_reports', ['id' => $reportid], '*', MUST_EXIST);
        $reconcile = new \ReflectionMethod(report_builder::class, 'synchronise_lifecycle_context');
        $reconcile->setAccessible(true);
        $reconcile->invoke(null, $report);

        $finding = $DB->get_record('local_ua_items', ['id' => $findingid], '*', MUST_EXIST);
        $this->assertSame('reviewed', $finding->status);
        $this->assertTrue($DB->record_exists('local_ua_audit', [
            'reportid' => $reportid,
            'action' => 'finding_reviewed',
            'targetid' => $findingid,
        ]));
    }
}
