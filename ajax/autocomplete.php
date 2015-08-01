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

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
$directory = new local_bulk_email_directory\local\directory();

$type = required_param('type', PARAM_RAW);
$term = required_param('term', PARAM_RAW);

header('Content-Type: application/json');

if ($type === 'list') {

    $data = $directory->searchlists($term);
    echo json_encode($data);

} else if ($type === 'email') {

    $data = $directory->searchemails($term);
    echo json_encode($data);

}
