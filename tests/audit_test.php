<?php
// This file is part of Moodle - https://moodle.org/
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

namespace auth_suap;

/**
 * Testes da ponte de auditoria (funciona com ou sem local_suap).
 *
 * @package     auth_suap
 * @copyright   2026 IFRN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \auth_suap\audit
 */
final class audit_test extends \advanced_testcase {
    public function test_update_user_changes_name(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Antes']);
        audit::set_context('task:teste');
        audit::update_user((object) ['id' => $user->id, 'firstname' => 'Depois'], false, false);
        $this->assertSame('Depois', $DB->get_field('user', 'firstname', ['id' => $user->id]));
    }

    public function test_set_picture_updates_user(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        audit::set_picture((int) $user->id, 5);
        $this->assertEquals(5, $DB->get_field('user', 'picture', ['id' => $user->id]));
    }

    public function test_around_returns_callable_result(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->assertSame('ok', audit::around((int) $user->id, fn() => 'ok'));
    }

    public function test_audit_row_written_when_local_suap_present(): void {
        global $DB;
        $this->resetAfterTest();
        if (!audit::available() || !$DB->get_manager()->table_exists('local_suap_user_audit')) {
            $this->markTestSkipped('local_suap com auditoria não instalado.');
        }
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Antes']);
        $DB->delete_records('local_suap_user_audit');
        audit::set_context('task:teste');
        audit::update_user((object) ['id' => $user->id, 'firstname' => 'Depois'], false, false);
        $row = $DB->get_record('local_suap_user_audit', ['targetuserid' => $user->id, 'field' => 'firstname']);
        $this->assertSame('Antes', $row->oldvalue);
        $this->assertSame('Depois', $row->newvalue);
        $this->assertSame('task:teste', $row->actorlabel);
    }
}
