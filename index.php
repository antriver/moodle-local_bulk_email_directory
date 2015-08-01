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
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$PAGE->requires->js('/local/bulk_email_directory/assets/js/js.js');
$PAGE->requires->css('/local/bulk_email_directory/assets/css/style.css');

echo $OUTPUT->header();

$list = optional_param('list', false, PARAM_RAW);
$email = optional_param('email', false, PARAM_RAW);

?>
<div id="local_bulk_email_directory" class="container-fluid">


    <div class="row-fluid">

        <div class="span6 <?php echo (!empty($email) ? 'dimmed' : ''); ?>">

            <h2><i class="fa fa-list"></i> See Who's On A List</h2>

            <form action="." method="get">

                <div class="row-fluid">
                    <div class="span10">
                        <input id="list-input" name="list" class="input-block-level" type="text" placeholder="Start typing a list name" value="<?php echo $list; ?>" />
                    </div>
                    <div class="span2">
                        <button type="submit" class="btn btn-block btn-primary">Search</button>
                    </div>
                </div>

                <span class="help-block"><strong>e.g.</strong> usebccparentsALL<?php echo $directory->listsuffix; ?></span>

            </form>

        </div>

        <div class="span6 <?php echo (!empty($list) ? 'dimmed' : ''); ?>">

            <h2><i class="fa fa-envelope"></i> Find Lists An Address Appears On</h2>

            <form action="." method="get">

                <div class="row-fluid">
                    <div class="span10">
                        <input id="email-input" name="email" class="input-block-level" type="text" placeholder="Start typing an email address" value="<?php echo $email; ?>" />
                    </div>
                    <div class="span2">
                        <button type="submit" class="btn btn-block btn-primary">Search</button>
                    </div>
                </div>

                <span class="help-block">This can be a student, teacher, or parent. <strong>e.g.</strong> happyparent@example.com</span>

            </form>

        </div>

    </div>


<?php

if (!empty($list)) {

    $listemails = $directory->getlistemails($list);

    if ($listemails === null) {

        ?>
        <div class="alert alert-danger">
            <i class="fa fa-info-circle"></i> No lists called <strong><?php echo htmlspecialchars($list, ENT_QUOTES, 'UTF-8'); ?></strong> were found.
        </div>
        <?php

    } else if (empty($listemails)) {

        ?>
        <div class="alert alert-danger">
            <i class="fa fa-info-circle"></i> The list <strong><?php echo htmlspecialchars($list, ENT_QUOTES, 'UTF-8'); ?></strong> is empty.
        </div>
        <?php

    } else {

        echo '<h3><i class="fa fa-list-alt"></i>
        <strong>' . htmlspecialchars($list, ENT_QUOTES, 'UTF-8') . '</strong>' . $directory->listsuffix . '
        <a class="btn btn-info" href="' . $directory->getmailtolink($list) . '"><i class="fa fa-envelope"></i> Send Email To List</a>
        </h3>';

        echo '<ul class="listmembers">';
        foreach ($listemails as $listemail) {
            echo '<li>';
                echo '<a class="btn btn-mini btn-success" href="?email=' . $listemail . '"><i class="fa fa-users"></i> View Other Lists</a> ';
                echo '<a class="btn btn-mini btn-info" href="mailto:' . $listemail . '"><i class="fa fa-envelope"></i> Send Email To User</a> ';
                echo $listemail;
             echo '</li>';
        }
        echo '</ul>';

    }
}

if (!empty($email)) {
    ?>
    <h3><i class="fa fa-search"></i> &quot;<strong><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></strong>&quot; Appears On These Lists</h3>
    <?php

    $lists = $directory->getlistsforemail($email);

    if (empty($lists)) {
        ?>
        <div class="alert alert-danger">
            <i class="fa fa-info-circle"></i> <strong><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></strong> was not found on any lists.
        </div>
        <?php
    } else {
        echo '<ul class="emaillists">';

        foreach ($lists as $list) {
            echo '<li>';
                echo '<a class="btn btn-mini btn-success" href="?list=' . $list . '"><i class="fa fa-users"></i> View List</a> ';
                echo '<a class="btn btn-mini btn-info" href="' . $directory->getmailtolink($list) . '"><i class="fa fa-envelope"></i> Send Email To List</a> ';
                echo '<strong>' . $list . '</strong>' . $directory->listsuffix;
             echo '</li>';
        }

        echo '</ul>';
    }

}

?>
</div>
<?php

echo $OUTPUT->footer();
