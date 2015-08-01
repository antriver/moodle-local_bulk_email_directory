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

    public $listsuffix = '@student.ssis-suzhou.net';

    private $loaded = false;
    private $data;

    public function __construct() {
        global $CFG;
        $this->path = $CFG->dataroot . '/' . $this->filename;

        require_once($CFG->dirroot . '/cohort/lib.php');
        $this->checkpermissions();
    }

    /**
     * Check if the current logged in user has permission to view bulk emails.
     * @throws \Exception
     * @return boolean
     */
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

    /**
     * Read and parse the data file into memory.
     * @return boolean
     */
    private function loaddata() {
        if ($this->loaded) {
            return true;
        }

        if (!is_readable($this->path)) {
            throw new Exception("Unable to read bulk email data file.");
        }

        $contents = file_get_contents($this->path);
        if (empty($contents)) {
            throw new Exception("Bulk email data file is empty.");
        }

        $data = json_decode($contents, true);
        unset($contents);
        if (empty($data)) {
            throw new Exception("Unable to parse JSON in bulk email data file.");
        }

        $this->data = $data;
        $this->loaded = true;
        return true;
    }

    /**
     * Returns all list names.
     * @return array
     */
    public function getalllists() {
        $this->loaddata();

        $lists = array();
        foreach ($this->data as $section => $sectionlists) {
            $sectionlists = array_keys($sectionlists);
            $lists += $sectionlists;
        }

        sort($lists);
        return $lists;
    }

    /**
     * Returns lists that contain the given search term in the name.
     * @param  string $query
     * @return array
     */
    public function searchlists($query) {
        $lists = $this->getalllists();

        $lists = array_filter($lists, function($name) use ($query) {
            return stripos($name, $query) !== false;
        });

        sort($lists);
        return $lists;
    }

    /**
     * Returns the email addresses on the given list name.
     * @param  string $list
     * @return array
     */
    public function getlistemails($list) {
        $this->loaddata();

        foreach ($this->data as $section => $sectionlists) {
            foreach ($sectionlists as $listname => $listemails) {
                if ($listname === $list) {
                    sort($listemails);
                    return $listemails;
                }
            }
        }

        return null;
    }

    /**
     * Returns all unique email addresses that appear on any list.
     * @return array
     */
    public function getallemails() {
        $this->loaddata();

        $emails = array();
        foreach ($this->data as $section => $sectionlists) {
            foreach ($sectionlists as $listname => $listemails) {
                $emails += $listemails;
            }
        }

        $emails = array_unique($emails);
        sort($emails);
        return $emails;
    }

    /**
     * Returns email addresses that appear on any list that contain the given search term.
     * @param  string $query
     * @return array
     */
    public function searchemails($query) {
        $emails = $this->getallemails();

        $emails = array_filter($emails, function($email) use ($query) {
            return stripos($email, $query) !== false;
        });

        sort($emails);
        return $emails;
    }

    /**
     * Returns all lists that the given email address appears on.
     * @param  string $email
     * @return array
     */
    public function getlistsforemail($email) {
        $this->loaddata();

        $lists = array();
        foreach ($this->data as $section => $sectionlists) {
            foreach ($sectionlists as $listname => $listemails) {
                if (in_array($email, $listemails)) {
                    $lists[] = $listname;
                }
            }
        }

        sort($lists);
        return $lists;
    }

    public function getmailtolink($list) {

        if (stripos($list, 'usebcc') === 0) {
            return 'mailto:?bcc=' . $list . $this->listsuffix;
        }

        return 'mailto:' . $list . $this->listsuffix;
    }

}
