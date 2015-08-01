<?php

namespace local_bulk_email_directory\local;

use Exception;

class directory
{
    // Location of the data file
    private $filename = 'bulk-email-data.json';
    private $path = null;

    private $data;

    public function __construct()
    {
        global $CFG;
        $this->path = $CFG->dataroot . '/' . $this->filename;
        $this->loadData();
    }

    private function loadData()
    {
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
