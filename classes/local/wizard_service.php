<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_upgradeassistant\local;

/**
 * Builds the operational wizard data without coupling it to the page controller.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class wizard_service {
    /**
     * Build checklist rows used by the preparation step.
     *
     * @param array $wizardstate Current wizard state.
     * @param array $env Environment data.
     * @param array $capabilities User capabilities.
     * @return array
     */
    public static function checklist(array $wizardstate, array $env, array $capabilities): array {
        $maintenanceactive = checklist_manager::is_maintenance_mode_active();
        $canmanage = !empty($capabilities['canmanage']);
        $canconfigure = !empty($capabilities['canconfigure'])
            && has_capability('moodle/site:config', \context_system::instance());

        $items = [];
        $items[] = self::item(
            'maintenance',
            get_string('checkmaintenance', 'local_upgradeassistant'),
            get_string('checkmaintenancedesc', 'local_upgradeassistant'),
            self::done($wizardstate, 'maintenance') || $maintenanceactive,
            '',
            $canconfigure ? ($maintenanceactive
                ? self::button(
                    'maintenanceoff',
                    get_string('disablemaintenance', 'local_upgradeassistant'),
                    'btn btn-outline-secondary'
                )
                : self::button(
                    'maintenanceon',
                    get_string('enablemaintenance', 'local_upgradeassistant'),
                    'btn btn-primary'
                )) : null,
            $maintenanceactive ? get_string('maintenanceactive', 'local_upgradeassistant') : ''
        );

        $currenttheme = (string)($env['theme'] ?? '');
        $boostactive = $currenttheme === 'boost';
        $items[] = self::item(
            'boosttheme',
            get_string('checkboosttheme', 'local_upgradeassistant'),
            get_string(
                'checkboostthemedesc',
                'local_upgradeassistant',
                $currenttheme ?: get_string('notdetected', 'local_upgradeassistant')
            ),
            self::done($wizardstate, 'boosttheme') || $boostactive,
            get_string('currenttheme', 'local_upgradeassistant') . ': '
                . ($currenttheme ?: get_string('notdetected', 'local_upgradeassistant')),
            $canconfigure ? ($boostactive
                ? self::button(
                    'confirmboosttheme',
                    get_string('verifyboosttheme', 'local_upgradeassistant'),
                    'btn btn-outline-success'
                )
                : self::button(
                    'switchtoboosttheme',
                    get_string('switchtoboosttheme', 'local_upgradeassistant'),
                    'btn btn-primary'
                )) : null,
            $boostactive ? get_string('boostthemeactive', 'local_upgradeassistant') : ''
        );

        $items[] = self::item(
            'backupdb',
            get_string('checkbackupdb', 'local_upgradeassistant'),
            get_string('checkbackupdbdesc', 'local_upgradeassistant'),
            self::done($wizardstate, 'backupdb'),
            get_string('backupdbfilename', 'local_upgradeassistant'),
            $canmanage ? self::button(
                'completestep',
                get_string('markcomplete', 'local_upgradeassistant'),
                'btn btn-outline-success',
                ['stepid' => 'backupdb']
            ) : null
        );
        $items[] = self::item(
            'backupcode',
            get_string('checkbackupcode', 'local_upgradeassistant'),
            get_string('checkbackupcodedesc', 'local_upgradeassistant'),
            self::done($wizardstate, 'backupcode'),
            (string)($env['dirroot'] ?? ''),
            $canmanage ? self::button(
                'completestep',
                get_string('markcomplete', 'local_upgradeassistant'),
                'btn btn-outline-success',
                ['stepid' => 'backupcode']
            ) : null
        );
        $items[] = self::item(
            'backupdata',
            get_string('checkbackupdata', 'local_upgradeassistant'),
            get_string('checkbackupdatadesc', 'local_upgradeassistant'),
            self::done($wizardstate, 'backupdata'),
            (string)($env['dataroot'] ?? ''),
            $canmanage ? self::button(
                'completestep',
                get_string('markcomplete', 'local_upgradeassistant'),
                'btn btn-outline-success',
                ['stepid' => 'backupdata']
            ) : null
        );
        $items[] = self::item(
            'purgecaches',
            get_string('checkpurgecaches', 'local_upgradeassistant'),
            get_string('checkpurgecachesdesc', 'local_upgradeassistant'),
            self::done($wizardstate, 'purgecaches'),
            '',
            $canconfigure ? self::button(
                'purgecaches',
                get_string('purgecaches', 'local_upgradeassistant'),
                'btn btn-outline-primary'
            ) : null
        );
        $items[] = self::item(
            'confirmtarget',
            get_string('checkconfirmtarget', 'local_upgradeassistant'),
            get_string('checkconfirmtargetdesc', 'local_upgradeassistant'),
            self::done($wizardstate, 'confirmtarget'),
            '',
            $canmanage ? self::button(
                'completestep',
                get_string('markcomplete', 'local_upgradeassistant'),
                'btn btn-outline-success',
                ['stepid' => 'confirmtarget']
            ) : null
        );
        $items[] = self::item(
            'servercompat',
            get_string('checkservercompat', 'local_upgradeassistant'),
            get_string(
                'checkservercompatdesc',
                'local_upgradeassistant',
                (string)($env['phpversion'] ?? '') . ' / ' . (string)($env['dbtype'] ?? '')
            ),
            self::done($wizardstate, 'servercompat'),
            '',
            $canmanage ? self::button(
                'completestep',
                get_string('markverified', 'local_upgradeassistant'),
                'btn btn-outline-success',
                ['stepid' => 'servercompat']
            ) : null
        );

        foreach ($items as $index => $item) {
            $items[$index]['icon'] = self::item_icon($item['id']);
        }

        return $items;
    }

    /**
     * Build final replacement instructions.
     *
     * @param array|null $selectedtarget Selected target.
     * @param array|null $analysis Route analysis.
     * @return array
     */
    public static function instructions(?array $selectedtarget, ?array $analysis): array {
        global $CFG;

        $data = [
            'available' => false,
            'blocked' => false,
            'warning' => '',
            'message' => get_string('instructionsnotarget', 'local_upgradeassistant'),
            'steps' => [],
            'targethaspublic' => false,
        ];

        if ($selectedtarget === null) {
            return $data;
        }
        if ($analysis !== null && ($analysis['status'] ?? '') === 'error') {
            $data['blocked'] = true;
            $data['message'] = get_string('instructionsblocked', 'local_upgradeassistant');
            return $data;
        }

        $currentpath = realpath($CFG->dirroot) ?: $CFG->dirroot;
        $targetpath = realpath($selectedtarget['path']) ?: $selectedtarget['path'];
        $targethaspublic = !empty($selectedtarget['haspublic']);
        $targetapproot = (string)($selectedtarget['approot'] ?? $targetpath);
        $targetapproot = realpath($targetapproot) ?: $targetapproot;
        $targetpublicpath = (string)($selectedtarget['publicpath'] ?? '');
        if ($targetpublicpath !== '') {
            $targetpublicpath = realpath($targetpublicpath) ?: $targetpublicpath;
        }
        if ($targethaspublic && $targetpublicpath === '') {
            $targetpublicpath = $targetapproot . DIRECTORY_SEPARATOR . 'public';
        }

        $data['available'] = true;
        $data['message'] = '';
        $data['targethaspublic'] = $targethaspublic;
        if ($analysis !== null && empty($analysis['directallowed'])) {
            $data['warning'] = get_string('instructionsnotdirect', 'local_upgradeassistant');
        }

        if ($targethaspublic) {
            // Moodle 5.1+ keeps the application root above the web-accessible public directory.
            // For a production cutover, the prepared application root replaces the old application root by name,
            // while the production domain is reconfigured to serve the final /public directory.
            $currentapproot = $currentpath;
            if (strtolower(basename($currentapproot)) === 'public') {
                $currentapproot = dirname($currentapproot);
            }

            $currentparent = dirname($currentapproot);
            $currentfolder = basename($currentapproot);
            $targetfolder = basename($targetapproot);
            $currentbranchlabel = detector::branch_label(
                (string)($CFG->branch ?? ''),
                (string)($CFG->release ?? '')
            );
            $branchsuffix = preg_replace('/[^0-9]+/', '', $currentbranchlabel);
            $branchsuffix = $branchsuffix !== '' ? '_moodle' . $branchsuffix : '';
            $oldfolder = $currentfolder . $branchsuffix . '_old_' . date('Y_m_d_His');
            $oldpath = $currentparent . DIRECTORY_SEPARATOR . $oldfolder;
            $newapproot = $currentparent . DIRECTORY_SEPARATOR . $currentfolder;
            $newpublicpath = $newapproot . DIRECTORY_SEPARATOR . 'public';
            $oldconfig = $oldpath . DIRECTORY_SEPARATOR . 'config.php';
            $newconfig = $newapproot . DIRECTORY_SEPARATOR . 'config.php';
            $upgradecli = 'php ' . $newpublicpath . DIRECTORY_SEPARATOR . 'admin'
                . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'upgrade.php --non-interactive';
            $purgecli = 'php ' . $newpublicpath . DIRECTORY_SEPARATOR . 'admin'
                . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'purge_caches.php';
            $croncli = 'php ' . $newpublicpath . DIRECTORY_SEPARATOR . 'admin'
                . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'cron.php';

            $data['steps'] = [
                ['text' => get_string('instructionpublicpreflight', 'local_upgradeassistant'), 'codes' => []],
                ['text' => get_string('instructionopenfilemanager', 'local_upgradeassistant'), 'codes' => []],
                ['text' => get_string('instructiontargetpublicdetected', 'local_upgradeassistant'), 'codes' => [
                    ['value' => $targetapproot], ['value' => $targetpublicpath],
                ]],
                ['text' => get_string('instructionpublicrenamecurrent', 'local_upgradeassistant'), 'codes' => [
                    ['value' => $currentapproot], ['value' => $oldpath],
                ]],
                ['text' => get_string('instructionpublicrenametarget', 'local_upgradeassistant'), 'codes' => [
                    ['value' => $targetapproot], ['value' => $newapproot],
                ]],
                ['text' => get_string('instructionpublicfinalroot', 'local_upgradeassistant'), 'codes' => [
                    ['value' => $newapproot], ['value' => $newpublicpath],
                ]],
                ['text' => get_string('instructionpubliccopyconfig', 'local_upgradeassistant'), 'codes' => [
                    ['value' => $oldconfig], ['value' => $newconfig],
                ]],
                ['text' => get_string('instructionpublicconfigcheck', 'local_upgradeassistant'), 'codes' => [
                    ['value' => '$CFG->wwwroot = \'' . $CFG->wwwroot . '\';'],
                    ['value' => '$CFG->dataroot = \'' . $CFG->dataroot . '\';'],
                    ['value' => "require_once(__DIR__ . '/lib/setup.php');"],
                ]],
                ['text' => server_recommendation_engine::public_document_root_instruction(), 'codes' => [
                    ['value' => $newpublicpath],
                ]],
                ['text' => get_string('instructionpublicrouting', 'local_upgradeassistant'), 'codes' => []],
                ['text' => get_string('instructionrunupgrade', 'local_upgradeassistant'), 'codes' => [
                    ['value' => $CFG->wwwroot . '/admin/index.php'], ['value' => $upgradecli],
                ]],
                ['text' => get_string('instructionpublicfinish', 'local_upgradeassistant'), 'codes' => [
                    ['value' => $purgecli], ['value' => $croncli],
                ]],
                ['text' => get_string('instructionpublicdevnote', 'local_upgradeassistant', (object)[
                    'targetfolder' => $targetfolder,
                    'currentfolder' => $currentfolder,
                ]), 'codes' => []],
            ];
            return $data;
        }

        $currentparent = dirname($currentpath);
        $currentfolder = basename($currentpath);
        $targetfolder = basename($targetpath);
        $oldfolder = $currentfolder . '_old_' . date('Y_m_d_His');
        $oldpath = $currentparent . DIRECTORY_SEPARATOR . $oldfolder;
        $newtargetpath = $currentparent . DIRECTORY_SEPARATOR . $currentfolder;
        $upgradecli = 'php ' . $newtargetpath . DIRECTORY_SEPARATOR . 'admin'
            . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'upgrade.php --non-interactive';

        $data['steps'] = [
            ['text' => get_string('instructionopenfilemanager', 'local_upgradeassistant'), 'codes' => []],
            ['text' => get_string('instructionclassictargetdetected', 'local_upgradeassistant'), 'codes' => [['value'
                => $targetpath]]],
            ['text' => get_string('instructionlocatecurrent', 'local_upgradeassistant'), 'codes' => [['value' => $currentpath]]],
            ['text' => get_string('instructionrenamecurrent', 'local_upgradeassistant'), 'codes' => [['value' => $currentfolder]]],
            ['text' => get_string('instructionnewoldname', 'local_upgradeassistant'), 'codes' => [['value' => $oldfolder]]],
            ['text' => get_string('instructionlocatetarget', 'local_upgradeassistant'), 'codes' => [['value' => $targetpath]]],
            ['text' => get_string('instructionrenametarget', 'local_upgradeassistant'), 'codes' => [
                ['value' => $targetfolder], ['value' => $currentfolder],
            ]],
            ['text' => get_string('instructionnewtargetpath', 'local_upgradeassistant'), 'codes' => [['value' => $newtargetpath]]],
            ['text' => get_string('instructioncopyconfig', 'local_upgradeassistant'), 'codes' => [
                ['value' => $oldpath . DIRECTORY_SEPARATOR . 'config.php'],
                ['value' => $newtargetpath . DIRECTORY_SEPARATOR . 'config.php'],
            ]],
            ['text' => get_string('instructionrunupgrade', 'local_upgradeassistant'), 'codes' => [
                ['value' => $CFG->wwwroot . '/admin/index.php'], ['value' => $upgradecli],
            ]],
            ['text' => get_string('instructionfinish', 'local_upgradeassistant'), 'codes' => []],
        ];

        return $data;
    }

    /**
     * Check whether a wizard step is complete.
     *
     * @return bool
     */
    private static function done(array $wizardstate, string $stepid): bool {
        return !empty($wizardstate['completed'][$stepid]);
    }

    /**
     * Describe an action button for the template.
     *
     * @return array
     */
    private static function button(string $action, string $label, string $class, array $params = []): array {
        return [
            'action' => $action,
            'label' => $label,
            'class' => $class,
            'params' => array_map(static function ($key, $value): array {
                return ['name' => $key, 'value' => $value];
            }, array_keys($params), $params),
        ];
    }

    /**
     * Describe a wizard item for the template.
     *
     * @return array
     */
    private static function item(
        string $id,
        string $title,
        string $description,
        bool $completed,
        string $code,
        ?array $button,
        string $extrabadge = ''
    ): array {
        return compact('id', 'title', 'description', 'completed', 'code', 'button', 'extrabadge');
    }

    /**
     * Get the icon for a wizard item.
     *
     * @return string
     */
    private static function item_icon(string $id): string {
        $icons = [
            'maintenance' => 'fa-wrench',
            'boosttheme' => 'fa-paint-brush',
            'backupdb' => 'fa-database',
            'backupcode' => 'fa-code',
            'backupdata' => 'fa-folder-open',
            'purgecaches' => 'fa-refresh',
            'confirmtarget' => 'fa-check-circle',
            'servercompat' => 'fa-server',
        ];
        return $icons[$id] ?? 'fa-check';
    }
}
