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
 * Calculates the Pro pre-upgrade risk score.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class risk_assessor {
    /** Maximum risk score. */
    private const MAX_SCORE = 100;

    /**
     * Calculate score from findings.
     *
     * @param array $findings Report findings.
     * @return array Risk data.
     */
    public static function assess(array $findings): array {
        $score = 0;
        $criticalfound = false;
        $severitycounts = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'info' => 0,
        ];

        foreach ($findings as $finding) {
            $status = $finding['status'] ?? 'open';
            if (in_array($status, ['closed', 'resolved', 'reviewed', 'mitigated'], true)) {
                continue;
            }

            $severity = $finding['severity'] ?? 'info';
            if (!array_key_exists($severity, $severitycounts)) {
                $severity = 'info';
            }
            $severitycounts[$severity]++;

            if ($severity === 'critical') {
                $criticalfound = true;
            }

            $score += self::severity_weight($severity);
        }

        $score = min(self::MAX_SCORE, $score);
        if ($criticalfound && $score < 75) {
            $score = 75;
        }

        $level = self::level_from_score($score);

        return [
            'score' => $score,
            'level' => $level,
            'label' => get_string('risklevel' . $level, 'local_upgradeassistant'),
            'class' => self::class_from_level($level),
            'counts' => $severitycounts,
        ];
    }

    /**
     * Return point weight by severity.
     *
     * @param string $severity Severity key.
     * @return int
     */
    private static function severity_weight(string $severity): int {
        $weights = [
            'critical' => 35,
            'high' => 22,
            'medium' => 12,
            'low' => 5,
            'info' => 0,
        ];

        return $weights[$severity] ?? 0;
    }

    /**
     * Convert score to level.
     *
     * @param int $score Risk score.
     * @return string
     */
    private static function level_from_score(int $score): string {
        if ($score >= 75) {
            return 'critical';
        }
        if ($score >= 50) {
            return 'high';
        }
        if ($score >= 25) {
            return 'medium';
        }
        return 'low';
    }

    /**
     * Convert level to CSS class.
     *
     * @param string $level Risk level.
     * @return string
     */
    private static function class_from_level(string $level): string {
        if ($level === 'critical') {
            return 'ua-risk-critical';
        }
        if ($level === 'high') {
            return 'ua-risk-high';
        }
        if ($level === 'medium') {
            return 'ua-risk-medium';
        }
        return 'ua-risk-low';
    }
}
