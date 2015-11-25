<?php

/**
 * User: aviginsberg
 * IDE: PhpStorm.
 * Date: 11/24/15
 */
class api_errors
{

    protected $errors = Array('600' => 'Missing Account ID and/or Account Key.',
        '601' => 'Invalid Account ID/Key.',
        '602' => 'Failed to login. Invalid iCloud account email or password. If you have recently changed your iCloud password you will need to regenerate your ID & API key.',
        '699' => 'Invalid function.',
        '700' => 'Invalid note ID_Num.',
        '701' => 'The specified note ID_Num was not found in this iCloud account.',
        '702' => 'There were no results for your request.',
        '703' => 'You cannot un-delete a note that is not marked as deleted.',
        '704' => 'Missing required data. You must specify a Note_Subject and Note_Body to create a new note.',
        '705' => 'An error occurred while trying to create the note.',
        '706' => 'Missing required data. You must specify a Search_String.');






    function error($error_code)
    {
        return $this->errors[$error_code];
    }

}