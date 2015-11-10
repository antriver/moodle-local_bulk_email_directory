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

/**
 * Functions for the directory.
 *
 * @package    local_bulk_email_directory
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_bulk_email_directory\local;

use Exception;

/**
 * Functions for the directory.
 *
 * @package    local_bulk_email_directory
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class directory
{
    /**
     * Name of the data file.
     *
     * @var string
     */
    private $filename = 'bulk-email-data.json';

    /**
     * Path to the data file (filled by constructor).
     *
     * @var string
     */
    private $path;

    /**
     * Email domain to append to the name of bulk email lists.
     *
     * @var string
     */
    public $listsuffix = '@student.ssis-suzhou.net';

    /**
     * Has the data file been loaded?
     *
     * @var boolean
     */
    private $loaded = false;

    /**
     * Contains the contents of the data file.
     *
     * @var object
     */
    private $data;

    /**
     * Constructor.
     */
    public function __construct() {
        global $CFG;

        $this->path = $CFG->dataroot . '/' . $this->filename;

        require_once($CFG->dirroot . '/cohort/lib.php');
        $this->check_permissions();
    }

    /**
     * Check if the current logged in user has permission to view bulk emails.
     * Ideally this would use a capability check, but that requires a system level role assigned to all teachers,
     * so it checks user is in a cohort instead.
     * FIXME: Hardcoded for SSIS
     *
     * @throws Exception
     * @return boolean
     */
    private function check_permissions() {
        global $USER;

        require_login();

        if (is_siteadmin()) {
            return true;
        }

        $cohortids = array(
            73, // ID of the teachersALL cohort.
            114, // ID of the secretariesALL cohort.
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
     *
     * @throws Exception
     * @return boolean
     */
    private function load_data() {

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
     * Returns all list names, organised by section.
     *
     * @return array
     */
    public function get_all_lists() {

        $this->load_data();

        $lists = array();
        foreach ($this->data as $sectionname => $sectionlists) {
            ksort($sectionlists);
            $lists[$sectionname] = array_keys($sectionlists);
        }

        return $lists;
    }

    /**
     * Returns lists that contain the given search term in the name.
     *
     * @param  string $query
     * @param  bool $flatten If true will return an array of just list names.
     *                       If false will return list names organised by section.
     * @return array
     */
    public function search_lists($query, $flatten = false) {

        $sections = $this->get_all_lists();

        foreach ($sections as $sectionname => &$sectionlists) {
            $sectionlists = array_filter($sectionlists, function($name) use ($query) {
                return stripos($name, $query) !== false;
            });
        }
        unset($sectionlists);

        if ($flatten) {
            $return = array();
            foreach ($sections as $sectionlists) {
                $return = array_merge($return, $sectionlists);
            }
            sort($return);
            return $return;
        }

        return $sections;
    }

    /**
     * Returns the email addresses on the given list name.
     *
     * @param  string $list
     * @return array
     */
    public function get_list_emails($list) {

        $this->load_data();

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
     *
     * @return array
     */
    public function get_all_emails() {

        $this->load_data();

        $emails = array();
        foreach ($this->data as $section => $sectionlists) {
            foreach ($sectionlists as $listname => $listemails) {
                $emails = array_merge($emails, $listemails);
            }
        }

        $emails = array_unique($emails);
        sort($emails);
        return $emails;
    }

    /**
     * Returns email addresses that appear on any list that contain the given search term.
     *
     * @param  string $query
     * @return array
     */
    public function search_emails($query) {

        $emails = $this->get_all_emails();

        $emails = array_filter($emails, function($email) use ($query) {
            return stripos($email, $query) !== false;
        });

        sort($emails);
        return $emails;
    }

    /**
     * Returns all lists that the given email address appears on.
     *
     * @param  string $email
     * @return array
     */
    public function get_lists_for_email($email) {

        $this->load_data();

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

    /**
     * Returns a mailto link for the given list name.
     *
     * @param  string $list List name
     * @return string
     */
    public function get_mailto_link($list) {

        if (stripos($list, 'usebcc') === 0) {
            return 'mailto:?bcc=' . $list . $this->listsuffix;
        }

        return 'mailto:' . $list . $this->listsuffix;
    }

}
