<?php

/**
 * iNotePrecipitator.php
 *
 * iCloud Notes Access Functions Class
 *
 * @version 0.0.6
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
    function Get_Note_Header_By_Note_Number($ID_Num)
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
            "Date" => trim($this->Get_Note_Header_By_Note_Number($ID_Num)['Date']),
            "H-Date" => trim($this->Get_Note_Header_By_Note_Number($ID_Num)['MailDate']),
            "Unix-Date" => trim($this->Get_Note_Header_By_Note_Number($ID_Num)['udate']),
            "Subject" => trim($this->Get_Note_Header_By_Note_Number($ID_Num)['Subject']),
            "ID-Num" => trim($this->Get_Note_Header_By_Note_Number($ID_Num)['Msgno']),
            "Size" => trim($this->Get_Note_Header_By_Note_Number($ID_Num)['Size']),
            "Note" => trim(quoted_printable_decode(imap_fetchbody($this->imap, $ID_Num, "1"))));
    }


    function Get_Note_Body_By_ID_Num($ID_Num)
    {
        return trim(quoted_printable_decode(imap_fetchbody($this->imap, $ID_Num, "1")));
    }

    function Get_Note_Subject_By_ID_Num($ID_Num)
    {
        return trim($this->Get_Note_Header_By_Note_Number($ID_Num)['Subject']);
    }

    function Get_Note_Size_By_ID_Num($ID_Num)
    {
        return trim($this->Get_Note_Header_By_Note_Number($ID_Num)['Size']);
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
            if ($this->Get_Note_Header_By_Note_Number($notenum_loop)['Deleted'] == "D") {
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
            if ($this->Get_Note_Header_By_Note_Number($notenum_loop)['Deleted'] != "D") {
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


    function Edit_Note_by_ID($ID_num, $note_text, $timestamp = FALSE){

    }




//List notes by date (newest first).
//Returns an associative 3d array with the date/timestamp as the key, [key]['subject'] holds the note subject, [key]['note'] holds the note data
//Return FALSE if there are no notes
    function Get_Notes_By_Date_Ascending()
    {

    }

//List notes by date (oldest first).
//Returns an associative 3d array with the date/timestamp as the key, [key]['subject'] holds the note subject, [key]['note'] holds the note data
//Return FALSE if there are no notes
    function Get_Notes_By_Date_Descending()
    {

    }

    function List_Note_IDs_By_Date_Ascending()
    {

    }

    function List_Note_IDs_By_Date_Descending()
    {

    }


    /**
     * Dev function for checking connection info, finding hooks, etc. May change at any time.
     *
     * @todo Remove in final version.
     *
     * @return void
     */
    function testconn()
    {
        // print_r(imap_list($this->imap, "{imap.mail.me.com:993/imap/ssl}Notes", "*"));
        $note_mailbox_info = get_object_vars(imap_mailboxmsginfo($this->imap));

        print_r($note_mailbox_info);

        //print "\n~~~Note Content:\n".imap_qprint(imap_fetchbody($this->imap,"70","1"))."\n\n\n";
        //die();

        $total_number_of_notes = $note_mailbox_info['Nmsgs'];

        for ($msgnumber = 1; $msgnumber <= $total_number_of_notes; $msgnumber++) {
            $header = imap_header($this->imap, $msgnumber);
            if (!$header) {
                echo "\nFailed on $msgnumber\n";
            }
            $usable_header = get_object_vars($header);
            if ($usable_header['Deleted'] == "D") {
                print "Deleted note found! Note ID Number: " . $msgnumber . "\n";
                print "Note Date: " . $usable_header['date'] . "\n";
                //print "\n~~~Note Content:\n".imap_qprint(imap_fetchbody($this->imap,$msgnumber,"1"))."\n\n\n";

                print "\n~~~Note Content:\n" . quoted_printable_decode(imap_fetchbody($this->imap, $msgnumber, "1")) . "\n\n\n";

                //print_r($usable_header);
                //die();

            }


            //print_r($header);

            //print "\n\nNote Status: ". $usable_header['Deleted']."\n";
        }


        //$emails = imap_search($this->imap,'ALL');
        // print_r($emails);

        // $UID = imap_uid($this->imap,"2");
        //  print imap_fetchbody($this->imap,"4","1");
        //  var_dump(imap_fetchstructure($this->imap, "4"));

        //   var_dump(imap_num_msg($this->imap));
        //  var_dump(imap_num_recent($this->imap));

        /*$msgnos = imap_search($this->imap, 'ALL');
        $uids   = imap_search($this->imap, 'ALL', SE_UID);

        print_r($msgnos);
        print_r($uids);*/


    }


}