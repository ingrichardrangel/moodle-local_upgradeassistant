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
 * Builds compact progress and navigation data for the assistant UI.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_service {
    /**
     * Build header summary and step navigation.
     *
     * @param array $env Current environment.
     * @param array|null $target Selected target.
     * @param array $checklist Checklist rows.
     * @param array $validationrows Validation rows.
     * @param array $plugins Plugin analysis rows.
     * @param array|null $analysis Upgrade path analysis.
     * @param array $report Latest or selected report.
     * @param int $requestedstep Requested active step.
     * @param \moodle_url $baseurl Base URL.
     * @return array
     */
    public static function build(
        array $env,
        ?array $target,
        array $checklist,
        array $validationrows,
        array $plugins,
        ?array $analysis,
        array $report,
        int $requestedstep,
        \moodle_url $baseurl
    ): array {
        $total = count($checklist);
        $completed = count(array_filter($checklist, static function (array $item): bool {
            return !empty($item['completed']);
        }));
        $pending = max(0, $total - $completed);
        $progress = $total > 0 ? (int)round(($completed / $total) * 100) : 0;

        $validationfailures = count(array_filter($validationrows, static function (array $row): bool {
            return ($row['status'] ?? '') === 'fail';
        }));
        $validationwarnings = count(array_filter($validationrows, static function (array $row): bool {
            return ($row['status'] ?? '') === 'warning';
        }));
        $pluginreview = count(array_filter($plugins, static function (array $plugin): bool {
            return !in_array(($plugin['compatibility'] ?? ''), ['compatible', 'core_removed'], true);
        }));
        $pluginblockers = count(array_filter($plugins, static function (array $plugin): bool {
            return in_array(($plugin['compatibility'] ?? ''), ['incompatible', 'dependency_missing', 'dependency_outdated'], true);
        }));
        $routeblocked = $analysis !== null && ($analysis['status'] ?? '') === 'error';
        $blockers = $validationfailures + $pluginblockers + ($routeblocked ? 1 : 0);

        $suggestedstep = 1;
        $continuestep = 2;
        if ($target !== null && ($pending > 0 || $blockers > 0 || $pluginreview > 0)) {
            $suggestedstep = 3;
            $continuestep = 3;
        } else if ($target !== null) {
            $suggestedstep = 4;
            $continuestep = 4;
        }
        $activestep = $requestedstep >= 1 && $requestedstep <= 4 ? $requestedstep : $suggestedstep;

        $steps = [];
        $labels = [
            1 => [get_string('stepdiagnosis', 'local_upgradeassistant'), 'fa-stethoscope'],
            2 => [get_string('steptarget', 'local_upgradeassistant'), 'fa-bullseye'],
            3 => [get_string('steppreparation', 'local_upgradeassistant'), 'fa-tasks'],
            4 => [get_string('stepexecution', 'local_upgradeassistant'), 'fa-rocket'],
        ];
        foreach ($labels as $number => [$label, $icon]) {
            $complete = false;
            if ($number === 1) {
                $complete = true;
            } else if ($number === 2) {
                $complete = $target !== null;
            } else if ($number === 3) {
                $complete = $target !== null && $pending === 0 && $blockers === 0;
            } else if ($number === 4) {
                $complete = !empty($report['available']);
            }
            $steps[] = [
                'number' => $number,
                'label' => $label,
                'icon' => $icon,
                'url' => (new \moodle_url($baseurl, ['step' => $number]))->out(false),
                'active' => $number === $activestep,
                'complete' => $complete,
                'available' => $number === 1 || $number === 2 || $target !== null,
            ];
        }

        $nextstep = min(4, $activestep + 1);
        $prevstep = max(1, $activestep - 1);

        return [
            'currentversion' => (string)($env['release'] ?? ''),
            'targetversion' => $target !== null
                ? (string)($target['release'] ?? '')
                : get_string('notselected', 'local_upgradeassistant'),
            'hastarget' => $target !== null,
            'pending' => $pending,
            'completed' => $completed,
            'total' => $total,
            'progress' => $progress,
            'blockers' => $blockers,
            'warnings' => $validationwarnings + max(0, $pluginreview - $pluginblockers),
            'pluginreview' => $pluginreview,
            'riskavailable' => !empty($report['available']),
            'riskscore' => !empty($report['available']) ? (int)$report['riskscore'] : 0,
            'risklevel' => !empty($report['available'])
                ? (string)$report['risklevel']
                : get_string('risknotcalculated', 'local_upgradeassistant'),
            'riskclass' => !empty($report['available']) ? (string)$report['riskclass'] : 'ua-risk-neutral',
            'activestep' => $activestep,
            'steps' => $steps,
            'showstep1' => $activestep === 1,
            'showstep2' => $activestep === 2,
            'showstep3' => $activestep === 3,
            'showstep4' => $activestep === 4,
            'previousurl' => (new \moodle_url($baseurl, ['step' => $prevstep]))->out(false),
            'nexturl' => (new \moodle_url($baseurl, ['step' => $nextstep]))->out(false),
            'hasprevious' => $activestep > 1,
            'hasnext' => $activestep < 4,
            'nextavailable' => $activestep === 1 || $target !== null,
            'continueurl' => (new \moodle_url($baseurl, ['step' => $continuestep]))->out(false),
        ];
    }
}
