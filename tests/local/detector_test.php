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
 * Tests for Moodle installation detection.
 *
 * @package    local_upgradeassistant
 * @category   test
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_upgradeassistant\local\detector
 */
final class detector_test extends \advanced_testcase {
    /**
     * Test classic Moodle root detection.
     *
     * @return void
     */
    public function test_read_moodle_version_detects_classic_root(): void {
        $this->resetAfterTest();

        $root = \make_temp_directory('local_upgradeassistant_classic');
        file_put_contents($root . '/version.php', "<?php\n\$release = '5.2.1';\n\$branch = '502';\n\$version = 2026042000;\n");

        $info = detector::read_moodle_version($root);

        $this->assertNotNull($info);
        $this->assertSame($root, $info['path']);
        $this->assertSame('502', $info['branch']);
        $this->assertFalse($info['haspublic']);
    }

    /**
     * Test selecting the public directory resolves the real Moodle app root.
     *
     * @return void
     */
    public function test_read_moodle_version_accepts_selected_public_directory(): void {
        $this->resetAfterTest();

        $root = \make_temp_directory('local_upgradeassistant_public_root');
        mkdir($root . '/public');
        file_put_contents($root . '/config.php', '<?php // Config placeholder.');
        file_put_contents($root . '/version.php', "<?php\n\$release = '5.1';\n\$branch = '501';\n\$version = 2025100600;\n");

        $info = detector::read_moodle_version($root . '/public');

        $this->assertNotNull($info);
        $this->assertSame($root, $info['path']);
        $this->assertSame($root . '/public', $info['publicpath']);
        $this->assertSame($root . '/config.php', $info['configpath']);
        $this->assertTrue($info['haspublic']);
    }

    /**
     * Test selecting a Moodle public directory with version.php inside public.
     *
     * @return void
     */
    public function test_read_moodle_version_accepts_selected_public_directory_with_public_version_file(): void {
        $this->resetAfterTest();

        $root = \make_temp_directory('local_upgradeassistant_public_selected');
        mkdir($root . '/public');
        file_put_contents($root . '/config.php', '<?php // Config placeholder outside public.');
        file_put_contents(
            $root . '/public/version.php',
            "<?php\n\$release = '5.2.1';\n\$branch = '502';\n\$version = 2026060800;\n"
        );

        $info = detector::read_moodle_version($root . '/public');

        $this->assertNotNull($info);
        $this->assertSame($root, $info['path']);
        $this->assertSame($root, $info['approot']);
        $this->assertSame($root . '/public', $info['publicpath']);
        $this->assertSame($root . '/config.php', $info['configpath']);
        $this->assertSame($root . '/public/version.php', $info['versionfile']);
        $this->assertSame('502', $info['branch']);
        $this->assertTrue($info['haspublic']);
    }

    /**
     * Test selecting the app root when version.php is inside public.
     *
     * @return void
     */
    public function test_read_moodle_version_detects_public_child_version_file_from_app_root(): void {
        $this->resetAfterTest();

        $root = \make_temp_directory('local_upgradeassistant_public_child_version');
        mkdir($root . '/public');
        file_put_contents($root . '/config.php', '<?php // Config placeholder outside public.');
        file_put_contents(
            $root . '/public/version.php',
            "<?php\n\$release = '5.2.1';\n\$branch = '502';\n\$version = 2026060800;\n"
        );

        $info = detector::read_moodle_version($root);

        $this->assertNotNull($info);
        $this->assertSame($root, $info['path']);
        $this->assertSame($root . '/public', $info['publicpath']);
        $this->assertSame($root . '/config.php', $info['configpath']);
        $this->assertSame($root . '/public/version.php', $info['versionfile']);
        $this->assertSame('502', $info['branch']);
        $this->assertTrue($info['haspublic']);
    }
}
