<?php

/**
 * iNotePrecipitator.php
 *
 * iCloud Notes Access Functions Class
 *
 * @version 0.0.9
 *
 * @author Avi Ginsberg
 *
 */
class iNotePrecipitator
{


    protected
        /**
         * IMAP connection object.
         * @var object
         */
        $imap,

        /**
         * The user's email address.
         * @var string
         */
        $email,

        /**
         * The user's username (everything before the @ symbol in the email address.) Derived from $email.
         * @var string
         */
        $username,

        /**
         * The FQDN of the user's email address (everything after the @ symbol in the email address.) Derived from $email.
         * @var string
         */
        $domain,

        /**
         * Associative array containing all regular notes from the user's icloud account. (regular notes are non-deleted notes)
         * @var array
         */
        $regular_notes,

        /**
         * Associative array containing all deleted notes from the user's icloud account.
         * @var array
         */
        $deleted_notes,

        /**
         * Associative array containing various properties of the notes storage mailbox including: Date, Driver, Mailbox name, Number of notes, Notes storage mailbox size.
         * @var array
         */
        $notes_mailbox_info,

        /**
         * Associative array containing all note header data.
         * @var array
         */
        $note_headers;

    /**
     * Boolean representing login success. Is set to TRUE when an icloud login has been completed successfully.
     * @var boolean
     */
    public $login_success = FALSE;

    //Constructor
    function __construct($email, $password)
    {
        //explode email address into username and domain
        $this->username = explode("@",$email)[0];
        $this->domain = explode("@",$email)[1];

        //Open the connection to iCloud notes mailbox
        $this->imap = imap_open('{imap.mail.me.com:993/imap/ssl}Notes', $this->username, $password);

        //set the login status
        if (!$this->imap) {
            $this->login_success = FALSE;
        } else {
            $this->login_success = TRUE;
        }

        //get our mailbox info
        $this->notes_mailbox_info = get_object_vars(imap_mailboxmsginfo($this->imap));
    }



    /**
     * @return int <u>Description:</u><br>Returns total number of notes (deleted and regular).
     */
    function Get_Total_Notes_Count()
    {
        return $this->notes_mailbox_info['Nmsgs'];
    }


    /**
     * @return int <u>Description:</u><br>Returns number of regular notes.
     */
    function Get_Regular_Notes_Count()
    {
        return $this->notes_mailbox_info['Nmsgs'] - $this->notes_mailbox_info['Deleted'];
    }


    /**
     * @return int <u>Description:</u><br>Returns number of deleted notes.
     */
    function Get_Deleted_Notes_Count()
    {
        return $this->notes_mailbox_info['Deleted'];
    }





    /**
     * Get the header data of a note and returns it as an associative array.
     *
     * @param int $ID_Num The numerical ID of the note.
     *
     * @return Array <u>Description:</u><br>An associative array containing note header data.<br>Common values are "Date", "Subject", and "Size". Other values may be present. These values differ based on iOS version that created the note.
     */
    function Get_Note_Header_By_ID_Num($ID_Num)
    {
        //if we already have the header data, return the requested header
        if (isset($this->note_headers)) {
            return $this->note_headers[$ID_Num - 1];

            //get all note header data and store it for future use
        } else {
            $this->note_headers = Array();
            for ($notenum_loop = 1; $notenum_loop <= $this->Get_Total_Notes_Count(); $notenum_loop++) {
                $this->note_headers[$notenum_loop - 1] = get_object_vars(imap_header($this->imap, $notenum_loop));
            }
            return $this->note_headers[$ID_Num - 1];
        }

    }


    function Get_Note_With_Header_Data_By_ID_Num($ID_Num)
    {
        return Array(
            "Date" => trim($this->Get_Note_Header_By_ID_Num($ID_Num)['Date']),
            "H-Date" => trim($this->Get_Note_Header_By_ID_Num($ID_Num)['MailDate']),
            "Unix-Date" => trim($this->Get_Note_Header_By_ID_Num($ID_Num)['udate']),
            "Subject" => trim($this->Get_Note_Header_By_ID_Num($ID_Num)['Subject']),
            "ID-Num" => trim($this->Get_Note_Header_By_ID_Num($ID_Num)['Msgno']),
            "Size" => trim($this->Get_Note_Header_By_ID_Num($ID_Num)['Size']),
            "Note" => trim(quoted_printable_decode(imap_fetchbody($this->imap, $ID_Num, "1"))));
    }

    /**
     * Get the body text of a specific note.
     *
     * @param int $ID_Num The numerical ID of the note.
     *
     * @return string <u>Description:</u><br>The body text of the note.
     */
    function Get_Note_Body_By_ID_Num($ID_Num)
    {
        return trim(quoted_printable_decode(imap_fetchbody($this->imap, $ID_Num, "1")));
    }

    /**
     * Get the subject of a specific note.
     *
     * @param int $ID_Num The numerical ID of the note.
     *
     * @return string <u>Description:</u><br>The subject of the note.
     */
    function Get_Note_Subject_By_ID_Num($ID_Num)
    {
        return trim($this->Get_Note_Header_By_ID_Num($ID_Num)['Subject']);
    }

    /**
     * Get the size (in bytes) of a specific note.
     *
     * @param int $ID_Num The numerical ID of the note.
     *
     * @return int <u>Description:</u><br>The size (in bytes) of the note.
     */
    function Get_Note_Size_By_ID_Num($ID_Num)
    {
        return trim($this->Get_Note_Header_By_ID_Num($ID_Num)['Size']);
    }

    /**
     * Check if the a note exists (based on note ID).
     *
     * @param int $ID_Num The numerical ID of the note.
     *
     * @return boolean <u>Description:</u><br>TRUE if the note exists. FALSE if the note does NOT exist.
     */
    function Check_If_Note_Exists_By_ID($ID_Num)
    {
        if(empty($this->Get_Note_Header_By_ID_Num($ID_Num)['Date']) && empty($this->Get_Note_Header_By_ID_Num($ID_Num)['Size']) && empty($this->Get_Note_Header_By_ID_Num($ID_Num)['Msgno']))
            return FALSE;
        else
            return TRUE;

    }


    /**
     * Gets all deleted notes and returns them in an associative array.
     *
     * @return Array <u>Description:</u><br>Returns an associative array of deleted notes formatted as:<br>Note_ID_Number => Array(Note & Header Data)
     */
    function Get_All_Deleted_Notes()
    {
        if (isset($this->deleted_notes))
            return $this->deleted_notes;


        $this->deleted_notes = Array();
        for ($notenum_loop = 1; $notenum_loop <= $this->Get_Total_Notes_Count(); $notenum_loop++) {
            if ($this->Get_Note_Header_By_ID_Num($notenum_loop)['Deleted'] == "D") {
                array_push($this->deleted_notes, $this->Get_Note_With_Header_Data_By_ID_Num($notenum_loop));
            }
        }

        return $this->deleted_notes;

    }


    /**
     * Gets all regular notes and returns them in an associative array.
     *
     * @return Array <u>Description:</u><br>Returns an associative array of regular notes formatted as:<br>Note_ID_Number => Array(Note & Header Data)
     */
    function Get_All_Regular_Notes()
    {
        if (isset($this->regular_notes))
            return $this->regular_notes;


        $this->regular_notes = Array();
        for ($notenum_loop = 1; $notenum_loop <= $this->Get_Total_Notes_Count(); $notenum_loop++) {
            if ($this->Get_Note_Header_By_ID_Num($notenum_loop)['Deleted'] != "D") {
                array_push($this->regular_notes, $this->Get_Note_With_Header_Data_By_ID_Num($notenum_loop));
            }
        }

        return $this->regular_notes;
    }






    /**
     * Create a new note with a given subject and body text.
     *
     * @param string $Note_Subject The note subject.
     * @param string $Note_Text The note body text.
     *
     * @return boolean <u>Description:</u><br>Returns TRUE if the note was created successfully and FALSE if the creation failed
     */
    function Create_New_Note($Note_Subject, $Note_Text)
    {
    $currenttime = strftime('%a, %d %b %Y %H:%M:%S %z');
    $note = "Date: $currenttime\nFrom: $this->email\nX-Uniform-Type-Identifier: com.apple.mail-note\nContent-Type: text/html;\nSubject: $Note_Subject\n\n$Note_Text";

    return imap_append($this->imap, "{imap.mail.me.com:993/imap/ssl}Notes", $note);
    }



    function Edit_Note_by_ID_Num($ID_Num, $note_text, $note_subject = FALSE, $timestamp = FALSE){
        //Array ( [Date] => Wed, 11 Nov 2015 12:10:02 -0500 [H-Date] => 11-Nov-2015 17:10:02 +0000 [Unix-Date] => 1447261802 [Subject] => Bark!!! [ID-Num] => 2 [Size] => 481 [Note] => Bark!!! )


    }



    /**
     * Delete a note by ID. (And force deletion)
     *
     * @param int $ID_Num The note's ID number.
     * @param boolean $Expunge Whether to expunge the note or not. Default is TRUE. See description.
     *
     * @return void <u>Description:</u><br>This method deletes a note by ID. If $Expunge is set to FALSE it will only mark the message for deletion (set a flag). Setting it to TRUE forces deletion of the message. This may also delete all other messages that have been marked for deletion.
     */
    function Delete_Note_By_ID($ID_Num, $Expunge = TRUE)
    {
        imap_delete($this->imap,$ID_Num);

        if($Expunge)
            imap_expunge($this->imap);
    }

    /**
     * UnDelete a note by ID (that has been marked as deleted)
     *
     * @param int $ID_Num The note's ID number.
     *
     * @return void <u>Description:</u><br>This method deletes a note by ID. It is designed to undelete notes that are marked as deleted but are still in the icloud.<u>Warning:</u><br> Setting $Expunge to TRUE will destroy any chance of recovering accidentally deleted notes.
     */
    function UnDelete_Note_By_ID($ID_Num)
    {
        imap_undelete($this->imap, $ID_Num);
    }

    /**
     * Permenantly deletes all notes that are marked as "deleted" but are still in the icloud.
     *
     *
     * @return void <u>Warning:</u><br> This will destroy any chance of recovering accidentally deleted notes.
     */
    function Expunge_Notes_Pending_Deletion()
    {
        imap_expunge($this->imap);
    }



    /**
     * Searches all regular notes (note body text) for a given string. Supports case sensitivity and regex.
     *
     * @param string $search_string The string to search for. (or the pattern to match if in regex mode)
     * @param int $search_mode The search mode. 0 for RegEx, 1 for CaSe SeNsItIvE, 2 for case insensitive.
     *
     * @return Array <u>Description:</u><br>Returns an associative array of regular notes that matched the search string or pattern formatted as:<br>Note_ID_Number => Array(Note & Header Data)
     */
    function Search_Notes($search_string, $search_mode)
    {
       $matches = Array();

       foreach ($this->Get_All_Regular_Notes() as $regnote)
       {

           switch($search_mode)
           {
               //ReGex search
               case 0:
                   if(preg_match("/".$search_string."/",$regnote['Note']) > 0)
                       $matches = $matches + $regnote;
                   break;

               //CaSe SeNsItIvE search
               case 1:
                   if(strstr($regnote['Note'],$search_string) != FALSE)
                       $matches = $matches + $regnote;
                   break;

               //case insensitive search
               case 2:
                   if(stristr($regnote['Note'],$search_string) != FALSE)
                       $matches = $matches + $regnote;
                   break;

               default:
                   die("Error: Invalid Search Mode given in function Search_Notes()");
           }

       }

       return $matches;

    }




    /**
     * Generate a list of note IDs in Ascending order (from oldest to newest note based on note timestamps).
     *
     * @return Array <u>Description:</u><br> Returns an array of note ID's with the oldest note ID in slot O of the array, and the newest note ID in the last slot of the array.
     */
    function List_Note_IDs_By_Date_Ascending()
    {
        $Sorted_IDs = Array();

        foreach($this->Get_All_Regular_Notes() as $Note_ID => $Note_Data_Array)
        {
            
            $Sorted_IDs = $Sorted_IDs + Array($Note_ID+1 => $Note_Data_Array['Unix-Date']);
        }

        asort($Sorted_IDs);

        return array_keys($Sorted_IDs);
    }

    //newest to oldest
    /**
     * Generate a list of note IDs in Descending order (from newest to oldest note based on note timestamps).
     *
     * @return Array <u>Description:</u><br> Returns an array of note ID's with the newest note ID in slot O of the array, and the oldest note ID in the last slot of the array.
     */
    function List_Note_IDs_By_Date_Descending()
    {
        $Sorted_IDs = Array();

        foreach($this->Get_All_Regular_Notes() as $Note_ID => $Note_Data_Array)
        {
            $Sorted_IDs = $Sorted_IDs + Array($Note_ID+1 => $Note_Data_Array['Unix-Date']);
        }

        arsort($Sorted_IDs);

        return array_keys($Sorted_IDs);
    }




}