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
 * Writes audit records for report actions.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class audit_logger {
    /** Audit table name. */
    private const TABLE = 'local_upgradeassistant_audit';

    /**
     * Add an audit entry.
     *
     * @param int $reportid Report ID.
     * @param string $action Action name.
     * @param string $targettype Target type.
     * @param int $targetid Target ID.
     * @param mixed $oldvalue Previous value.
     * @param mixed $newvalue New value.
     * @param string $note Optional note.
     * @return int Audit record ID.
     */
    public static function log(
        int $reportid,
        string $action,
        string $targettype,
        int $targetid = 0,
        $oldvalue = null,
        $newvalue = null,
        string $note = ''
    ): int {
        global $DB, $USER;

        $record = (object)[
            'reportid' => $reportid,
            'userid' => (int)($USER->id ?? 0),
            'action' => self::trim_value($action, 60),
            'targettype' => self::trim_value($targettype, 60),
            'targetid' => $targetid,
            'oldvalue' => self::encode_value($oldvalue),
            'newvalue' => self::encode_value($newvalue),
            'note' => self::trim_value($note, 2000),
            'ip' => self::trim_value(getremoteaddr() ?: '', 45),
            'useragent' => self::trim_value($_SERVER['HTTP_USER_AGENT'] ?? '', 255),
            'timecreated' => time(),
        ];

        return (int)$DB->insert_record(self::TABLE, $record);
    }

    /**
     * Encode an arbitrary audit payload.
     *
     * @param mixed $value Value to encode.
     * @return string
     */
    private static function encode_value($value): string {
        if ($value === null || $value === '') {
            return '';
        }
        if (is_scalar($value)) {
            return (string)$value;
        }
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $json === false ? '' : $json;
    }

    /**
     * Trim a value to a maximum length.
     *
     * @param string $value Value.
     * @param int $maxlength Maximum length.
     * @return string
     */
    private static function trim_value(string $value, int $maxlength): string {
        return \core_text::substr($value, 0, $maxlength);
    }
}
