<?php

/**
 * iNotePrecipitator.php
 * iCloud Notes Access Functions Class
 * by Avi Ginsberg
 *
 * IDE: PhpStorm.
 *
 */
class iNotePrecipitator
{

    //Variables
    protected $imap, $all_notes, $regular_notes, $deleted_notes, $notes_mailbox_info, $note_headers;
    public $login_success;

    //Constructor
    function __construct($email, $password)
    {
        //Open the connection to iCloud notes mailbox
        $this->imap = imap_open('{imap.mail.me.com:993/imap/ssl}Notes', $email, $password);

        //set the login status
        if (!$this->imap) {
            $this->login_success = FALSE;
        } else {
            $this->login_success = TRUE;
        }

        //get our mailbox info
        $this->notes_mailbox_info = get_object_vars(imap_mailboxmsginfo($this->imap));
    }

    //returns the number of deleted notes
    function Get_Deleted_Notes_Count()
    {
        return $this->notes_mailbox_info['Deleted'];
    }

    //returns total number of notes (deleted and regular)
    function Get_Total_Notes_Count()
    {
        return $this->notes_mailbox_info['Nmsgs'];
    }

    //returns the number of regular notes

    function Get_Regular_Notes_Count()
    {
        return $this->notes_mailbox_info['Nmsgs'] - $this->notes_mailbox_info['Deleted'];
    }

    function Get_Note_Header_By_Note_Number($notenumber)
    {
        //if we already have the header data, return the requested header
        if (isset($this->note_headers)) {
            return $this->note_headers[$notenumber - 1];

            //get all note header data and store it for future use
        } else {
            $this->note_headers = Array();
            for ($notenum_loop = 1; $notenum_loop <= $this->Get_Total_Notes_Count(); $notenum_loop++) {
                $this->note_headers[$notenum_loop - 1] = get_object_vars(imap_header($this->imap, $notenum_loop));
            }
            return $this->note_headers[$notenumber - 1];
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


    //test function to check connection
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
//
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



    //List notes by date (newest first).
    //Returns an associative 3d array with the date/timestamp as the key, [key]['subject'] holds the note subject, [key]['note'] holds the note data
    //Return FALSE if there are no notes
    function List_Notes_By_Date_Ascending()
    {

    }

    //List notes by date (oldest first).
    //Returns an associative 3d array with the date/timestamp as the key, [key]['subject'] holds the note subject, [key]['note'] holds the note data
    //Return FALSE if there are no notes
    function List_Notes_By_Date_Descending()
    {

    }

    //returns associative array: Note_ID_Number => Array(Note & Header Data)
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


}