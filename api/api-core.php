<?php
/**
 * iNotePrecipitator API Core Functionality Class
 *
 * Version 0.1.0
 *
 * Author: Avi Ginsberg
 * IDE: PhpStorm.
 */

require_once "../iNotePrecipitator.php";
require_once "api-authenticator.php";
require_once "api-errors.php";

/**Get our key, ID, and function.
 * If you are setting up your own version of this API you would probably get an email and password instead of an account id & key pair.
 * You would also need to set up your server to properly pass URL elements using mod_rewrite or something similar.
 */
$account_key = $_GET['key'];
$account_id = $_GET['account'];
$function = strtolower($_GET['f']);


//make an error codes object
$EC = new api_errors();


//function to return our JSON formatted response to the user and die.
function json_output($output_array)
{
    header('Content-Type: application/json');
    echo json_encode($output_array);
    die();
}


//Safety check to make sure we have an account id and key. This should never happen because the mod_rewrite regex should prevent it.
if(!isset($_GET['key'])||!isset($_GET['account']))
    json_output(Array('Status'=>'600','Description'=>$EC->error(600)));


//AUTHENTICATE
$API_Auth = new api_authenticator($account_id, $account_key);
if(!$API_Auth->authenticate())
    json_output(Array('Status'=>'601','Description'=>$EC->error(601)));





/** Create a new iNotePrecipitator object
 * We are using a custom authentication class here that returns an authenticated iNotePrecipitator object based on the API Account ID / Key pair passed in.
 * If you are going to implement this API for yourself need to create an iNotePrecipitator object like this:
 * $INP = new iNotePrecipitator($email, $password);
 * OR you need to build your own Authenticator class.
 * The Authenticator class used in this API is not published for security reasons.
 */
$INP = $API_Auth->getNewINP();

if(!$INP->login_success)
json_output(Array('Status'=>'602','Description'=>$EC->error(602)));




//Commands for API
switch ($function)
{
//GetTotalNotesCount
    case "gettotalnotescount":
        json_output(Array('Function' => 'GetTotalNotesCount', 'Status'=>'200', 'Result' => $INP->Get_Total_Notes_Count()));
        break;

//GetRegularNotesCount
    case "getregularnotescount":
        json_output(Array('Function' => 'GetRegularNotesCount', 'Status'=>'200', 'Result' => $INP->Get_Regular_Notes_Count()));
        break;

//GetDeletedNotesCount
    case "getdeletednotescount":
        json_output(Array('Function' => 'GetDeletedNotesCount','Status'=>'200',  'Result' => $INP->Get_Deleted_Notes_Count()));
        break;

//GetNoteHeaderByIDNum
    case "getnoteheaderbyidnum":
        //check if our input is numeric only
        if(preg_match('/^\d{1,7}$/',$_POST['ID_Num'])!=1)
            json_output(Array('Function' => 'GetNoteHeaderByIDNum', 'Status'=>'700',  'Description' => $EC->error(700)));

        //check if there is a note with that ID num
        if(!$INP->Check_If_Note_Exists_By_ID_Num($_POST['ID_Num']))
            json_output(Array('Function' => 'GetNoteHeaderByIDNum', 'Status'=>'701',  'Description' => $EC->error(701)));

        $result = $INP->Get_Note_Header_By_ID_Num($_POST['ID_Num']);

        json_output(Array('Function' => 'GetNoteHeaderByIDNum', 'Status'=>'200', 'Result' => $result));
        break;

//GetNoteWithHeaderDataByIDNum
    case "getnotewithheaderdatabyidnum":
        //check if our input is numeric only
        if(preg_match('/^\d{1,7}$/',$_POST['ID_Num'])!=1)
            json_output(Array('Function' => 'GetNoteWithHeaderDataByIDNum', 'Status'=>'700',  'Description' => $EC->error(700)));

        if(!$INP->Check_If_Note_Exists_By_ID_Num($_POST['ID_Num']))
            json_output(Array('Function' => 'GetNoteWithHeaderDataByIDNum', 'Status'=>'701',  'Description' => $EC->error(701)));


        $result = $INP->Get_Note_With_Header_Data_By_ID_Num($_POST['ID_Num']);

        json_output(Array('Function' => 'GetNoteWithHeaderDataByIDNum', 'Status'=>'200', 'Result' => $result));
        break;

//GetNoteBodyByIDNum
    case "getnotebodybyidnum":
        //check if our input is numeric only
        if(preg_match('/^\d{1,7}$/',$_POST['ID_Num'])!=1)
            json_output(Array('Function' => 'GetNoteBodyByIDNum', 'Status'=>'700',  'Description' => $EC->error(700)));

        if(!$INP->Check_If_Note_Exists_By_ID_Num($_POST['ID_Num']))
            json_output(Array('Function' => 'GetNoteBodyByIDNum', 'Status'=>'701',  'Description' => $EC->error(701)));

        $result = $INP->Get_Note_Body_By_ID_Num($_POST['ID_Num']);

        json_output(Array('Function' => 'GetNoteBodyByIDNum', 'Status'=>'200', 'Result' => $result));
        break;

//GetNoteSubjectByIDNum
    case "getnotesubjectbyidnum":
        //check if our input is numeric only
        if(preg_match('/^\d{1,7}$/',$_POST['ID_Num'])!=1)
            json_output(Array('Function' => 'GetNoteSubjectByIDNum', 'Status'=>'700',  'Description' => $EC->error(700)));

        if(!$INP->Check_If_Note_Exists_By_ID_Num($_POST['ID_Num']))
            json_output(Array('Function' => 'GetNoteSubjectByIDNum', 'Status'=>'701',  'Description' => $EC->error(701)));

        $result = $INP->Get_Note_Subject_By_ID_Num($_POST['ID_Num']);

        json_output(Array('Function' => 'GetNoteSubjectByIDNum', 'Status'=>'200', 'Result' => $result));
        break;

//GetNoteSizeByIDNum
    case "getnotesizebyidnum":
        //check if our input is numeric only
        if(preg_match('/^\d{1,7}$/',$_POST['ID_Num'])!=1)
            json_output(Array('Function' => 'GetNoteSizeByIDNum', 'Status'=>'700',  'Description' => $EC->error(700)));

        if(!$INP->Check_If_Note_Exists_By_ID_Num($_POST['ID_Num']))
            json_output(Array('Function' => 'GetNoteSizeByIDNum', 'Status'=>'701',  'Description' => $EC->error(701)));

        $result = $INP->Get_Note_Size_By_ID_Num($_POST['ID_Num']);

        json_output(Array('Function' => 'GetNoteSizeByIDNum', 'Status'=>'200', 'Result' => $result));
        break;

//GetAllDeletedNotes
    case "getalldeletednotes":
        //check if we have any deleted notes
        if($INP->Get_Deleted_Notes_Count()<1)
            json_output(Array('Function' => 'GetAllDeletedNotes', 'Status'=>'702',  'Description' => $EC->error(702)));

        $result = $INP->Get_All_Deleted_Notes();

        json_output(Array('Function' => 'GetAllDeletedNotes', 'Status'=>'200', 'Result' => $result));
        break;

//GetAllRegularNotes
    case "getallregularnotes":
        //check if we have any regular notes
        if($INP->Get_Regular_Notes_Count()<1)
            json_output(Array('Function' => 'GetAllRegularNotes', 'Status'=>'702',  'Description' => $EC->error(702)));

        $result = $INP->Get_All_Regular_Notes();

        json_output(Array('Function' => 'GetAllRegularNotes', 'Status'=>'200', 'Result' => $result));
        break;

//DeleteNoteByID
    case "deletenotebyid":
        //check if our input is numeric only
        if(preg_match('/^\d{1,7}$/',$_POST['ID_Num'])!=1)
            json_output(Array('Function' => 'DeleteNoteByID', 'Status'=>'700',  'Description' => $EC->error(700)));

        if(!$INP->Check_If_Note_Exists_By_ID_Num($_POST['ID_Num']))
            json_output(Array('Function' => 'DeleteNoteByID', 'Status'=>'701',  'Description' => $EC->error(701)));

        $INP->Delete_Note_By_ID($_POST['ID_Num'],FALSE);

        json_output(Array('Function' => 'DeleteNoteByID', 'Status'=>'200'));
        break;

//DeleteExpungeNoteByID
    case "deleteexpungenotebyid":
        //check if our input is numeric only
        if(preg_match('/^\d{1,7}$/',$_POST['ID_Num'])!=1)
            json_output(Array('Function' => 'DeleteExpungeNoteByID', 'Status'=>'700',  'Description' => $EC->error(700)));

        if(!$INP->Check_If_Note_Exists_By_ID_Num($_POST['ID_Num']))
            json_output(Array('Function' => 'DeleteExpungeNoteByID', 'Status'=>'701',  'Description' => $EC->error(701)));

        $INP->Delete_Note_By_ID($_POST['ID_Num'],TRUE);

        json_output(Array('Function' => 'DeleteExpungeNoteByID', 'Status'=>'200'));
        break;

//UndeleteNoteByID
    case "undeletenotebyid":
        //check if our input is numeric only
        if(preg_match('/^\d{1,7}$/',$_POST['ID_Num'])!=1)
            json_output(Array('Function' => 'UndeleteNoteByID', 'Status'=>'700',  'Description' => $EC->error(700)));

        if(!$INP->Check_If_Note_Exists_By_ID_Num($_POST['ID_Num']))
            json_output(Array('Function' => 'UndeleteNoteByID', 'Status'=>'701',  'Description' => $EC->error(701)));

        if(!$INP->Check_If_Note_Is_Deleted_By_ID_Num($_POST['ID_Num']))
            json_output(Array('Function' => 'UndeleteNoteByID', 'Status'=>'703',  'Description' => $EC->error(703)));

        $INP->UnDelete_Note_By_ID($_POST['ID_Num']);

        json_output(Array('Function' => 'UndeleteNoteByID', 'Status'=>'200'));
        break;

//CheckIfNoteExistsByIDNum
    case "checkifnoteexistsbyidnum":
        //check if our input is numeric only
        if(preg_match('/^\d{1,7}$/',$_POST['ID_Num'])!=1)
            json_output(Array('Function' => 'CheckIfNoteExistsByIDNum', 'Status'=>'700',  'Description' => $EC->error(700)));

        if($INP->Check_If_Note_Exists_By_ID_Num($_POST['ID_Num']))
            json_output(Array('Function' => 'CheckIfNoteExistsByIDNum', 'Status'=>'200', 'Result' => 'TRUE'));
        else
            json_output(Array('Function' => 'CheckIfNoteExistsByIDNum', 'Status'=>'200', 'Result' => 'FALSE'));
        break;

//CheckIfNoteIsDeletedByIDNum
    case "checkifnoteisdeletedbyidnum":
        //check if our input is numeric only
        if(preg_match('/^\d{1,7}$/',$_POST['ID_Num'])!=1)
            json_output(Array('Function' => 'CheckIfNoteIsDeletedByIDNum', 'Status'=>'700',  'Description' => $EC->error(700)));

        if(!$INP->Check_If_Note_Exists_By_ID_Num($_POST['ID_Num']))
            json_output(Array('Function' => 'CheckIfNoteIsDeletedByIDNum', 'Status'=>'701',  'Description' => $EC->error(701)));

        if($INP->Check_If_Note_Is_Deleted_By_ID_Num($_POST['ID_Num']))
            json_output(Array('Function' => 'CheckIfNoteIsDeletedByIDNum', 'Status'=>'200', 'Result' => 'TRUE'));
        else
            json_output(Array('Function' => 'CheckIfNoteIsDeletedByIDNum', 'Status'=>'200', 'Result' => 'FALSE'));
        break;

//ExpungeNotesPendingDeletion
    case "expungenotespendingdeletion":
        $INP->Expunge_Notes_Pending_Deletion();
        json_output(Array('Function' => 'ExpungeNotesPendingDeletion', 'Status'=>'200'));
        break;


//CreateNewNote
    case "createnewnote":
        //make sure we were given a note subject and body
        if(!isset($_POST['Note_Subject'])||!isset($_POST['Note_Body']))
            json_output(Array('Function' => 'CreateNewNote', 'Status'=>'704',  'Description' => $EC->error(704)));

        if($INP->Create_New_Note($_POST['Note_Subject'],$_POST['Note_Body']))
            json_output(Array('Function' => 'CreateNewNote', 'Status'=>'200'));
        else
            json_output(Array('Function' => 'CreateNewNote', 'Status'=>'705',  'Description' => $EC->error(705)));

        break;



//SearchNotes
    case "searchnotes":
        //make sure we were given a note subject and body
        if(!isset($_POST['Search_String']))
            json_output(Array('Function' => 'SearchNotes', 'Status'=>'706',  'Description' => $EC->error(706)));

        $result = $INP->Search_Notes($_POST['Search_String'],2);

        if(empty($result)||is_null($result))
             json_output(Array('Function' => 'SearchNotes', 'Status'=>'702',  'Description' => $EC->error(702)));

        json_output(Array('Function' => 'SearchNotes', 'Status'=>'200', 'Result' => $result));
        break;

//SearchNotesCaseSensitive
    case "searchnotescasesensitive":
        //make sure we were given a note subject and body
        if(!isset($_POST['Search_String']))
            json_output(Array('Function' => 'SearchNotesCaseSensitive', 'Status'=>'706',  'Description' => $EC->error(706)));

        $result = $INP->Search_Notes($_POST['Search_String'],1);

        if(empty($result)||is_null($result))
            json_output(Array('Function' => 'SearchNotesCaseSensitive', 'Status'=>'702',  'Description' => $EC->error(702)));

        json_output(Array('Function' => 'SearchNotesCaseSensitive', 'Status'=>'200', 'Result' => $result));
        break;


    default:
        json_output(Array('Function' => $_GET['f'], 'Status'=>'699', 'Description'=>$EC->error(699)));

}














