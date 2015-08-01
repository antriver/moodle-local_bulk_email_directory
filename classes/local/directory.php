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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_bulk_email_directory\local;

use Exception;

class directory
{
    // Location of the data file
    private $filename = 'bulk-email-data.json';
    private $path = null;

    private $data;

    public function __construct() {
        global $CFG;

        require_once($CFG->dirroot . '/cohort/lib.php');
        $this->checkpermissions();

        $this->path = $CFG->dataroot . '/' . $this->filename;
        $this->loaddata();
    }

    private function checkpermissions() {
        global $USER;

        // Ideally this would use a capability check, but that requires a system level role assigned to all teachers
        // require_capability('local/bulk_email_directory:view', context_system::instance());

        // Instead check the user is in a valid cohort
        // FIXME: Hardcoded for SSIS
        require_login();

        if (is_siteadmin()) {
            return true;
        }

        $cohortids = array(
            73, // teachersALL
            114, // secretariesALL
        );
        foreach ($cohortids as $cohortid) {
            if (cohort_is_member($cohortid, $USER->id)) {
                return true;
            }
        }

        throw new Exception("You don't have permission to do that.");
    }

    private function loaddata() {
        if (!is_readable($this->path)) {
            throw new Exception("Unable to read bulk email data file.");
        }

        $contents = file_get_contents($this->path);
        if (empty($contents)) {
            throw new Exception("Bulk email data file is empty.");
        }

        $data = json_decode($contents);
        unset($contents);
        if (empty($data)) {
            throw new Exception("Unable to parse JSON in bulk email data file.");
        }

        $this->data = $data;
        return true;
    }
}
