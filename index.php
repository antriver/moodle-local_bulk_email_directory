<?php

/**
 * @package    local_bulk_email_directory
 * @copyright  Anthony Kuske <www.anthonykuske.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require dirname(dirname(dirname(__FILE__))) . '/config.php';
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

print_object($directory);

echo $OUTPUT->footer();
