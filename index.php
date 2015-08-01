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
 * @package    local_bulk_email_directory
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
$directory = new local_bulk_email_directory\local\directory();

/**
 * Setup page
 */
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/bulk_email_directory/');
$PAGE->set_title(get_string('pagetitle', 'local_bulk_email_directory'));
$PAGE->set_heading(get_string('pageheading', 'local_bulk_email_directory'));
$PAGE->requires->jquery();

echo $OUTPUT->header();

?>

<div class="container-fluid">
    <div class="row-fluid">

        <div class="span6">
            <h2><i class="fa fa-envelope"></i> See Who's On A List</h2>
        </div>
        <div class="span6">
            <h2><i class="fa fa-envelope"></i> See Which List An Address Appears On</h2>
        </div>

    </div>
</div>

<?php
print_object($directory);

echo $OUTPUT->footer();
