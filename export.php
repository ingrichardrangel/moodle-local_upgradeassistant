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

/**
 * PDF export endpoint for Smart Upgrade Assistant reports.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_upgradeassistant\local\pdf_exporter;

require_login();
admin_externalpage_setup('local_upgradeassistant');
$context = context_system::instance();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    throw new moodle_exception('invalidrequest', 'error');
}

require_capability('local/upgradeassistant:viewreports', $context);
require_capability('local/upgradeassistant:export', $context);
require_sesskey();

$reportid = required_param('reportid', PARAM_INT);
$type = required_param('type', PARAM_ALPHA);
$redacted = optional_param('redacted', 1, PARAM_BOOL);

if (!in_array($type, ['pdf', 'html'], true)) {
    throw new moodle_exception('invalidexporttype', 'local_upgradeassistant');
}

if (!$redacted) {
    require_capability('moodle/site:config', $context);
    require_capability('local/upgradeassistant:viewsensitive', $context);
}

if ($type === 'html') {
    pdf_exporter::download_html($reportid, (bool)$redacted);
} else {
    pdf_exporter::download($reportid, (bool)$redacted);
}
exit;
