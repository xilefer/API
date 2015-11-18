<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.10.2015
 * Time: 15:16
 */

include_once 'init.php';



$request = new \request\Request();
$response = new \response\response();
$Users = new \Users\User();
$Events = new \Events\event();
$Groups = new \Groups\Group();
$Locations = new \Location\location();
$Comments = new \Events\comment();
$URI= $request->getRequestURI();
$method= $request->getMethod();
$URIs=explode("/",$URI);
$return = new \methodreturn\createreturn();
$main = new \enum\tables\main();
if($URIs[2]=='Login' and isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])){
    $Username=$_SERVER['PHP_AUTH_USER'];
    $PW= $_SERVER['PHP_AUTH_PW'];
    $Auth=$Users->verifyUser($Username,$PW);
    if($Auth == 'Error'){
        $return->createReturn(null,\enum\statuscodes::UNAUTHORIZED,\enum\returncodes::Error_WrongUsernameorPassword);
    }
    else
    {
        $data=$Users->loginUser($Username);
        $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
    }
}
elseif(isset($_SERVER['PHP_AUTH_USER']))
{
    $count = count($URIs);
    $lastposition = $count-1;
    $LoginToken=$_SERVER['PHP_AUTH_USER'];
    $ID=$Users->verifyToken($LoginToken);
    if(!$ID){
        $return->createReturn(null,\enum\statuscodes::UNAUTHORIZED,\enum\returncodes::Error_WrongUsernameorLoginToken);
    }
    else{$UserID=$ID;}
}
elseif($URIs[2]=="Users" and $URIs[3]=="activate")
{
    $code=$Users->activateAccount($URIs[4],$URIs[5]);
    if($code)
    {
        $body="Ihr Account wurde erfolgreich aktiviert.";
        $response->setBody($body);
        $response->setStatuscode(\enum\statuscodes::OK);
        $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'text/html');
        $response->returnResponse();
        exit;
    }
    else
    {
        $body="Bei der Aktivierung ihres Accounts ist ein Fehler aufgetreten.";
        $response->setBody($body);
        $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
        $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'text/html');
        $response->returnResponse();
        exit;
    }
}
else
{
    $return->createReturn(null,\enum\statuscodes::UNAUTHORIZED,\enum\returncodes::Error_AuthenticationRequired);
    exit;
}
switch ($method) {

    case (\enum\Methods::GET):
        switch ($URIs[2]) {

            case ("Users"):
                $data=$Users->getUser($UserID);
                if($data == 'Error')
                {
                    $return->createReturn(null,\enum\statuscodes::NOT_FOUND,\enum\returncodes::Error_UserDoesnotexist);
                }
                else {
                    $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
                }
                break;

            case ('Groups'):
                switch($URIs[3]){
                    case('Group'):
                        if(count($URIs) != 5){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data=$Groups->getGroupsForUser($URIs[4]);
                        if($data == 32){
                            $return->createReturn(null,enum\statuscodes::NOT_FOUND,enum\returncodes::Error_UserHasNoGroups);
                        }
                        else if($data == 12){
                            $return->createReturn(null,enum\statuscodes::BAD_REQUEST,enum\returncodes::Error_UserDoesnotexist);
                        }
                        else {
                            $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
                        }
                        break;
                    case('Search'):
                        if(count($URIs) != 5){
                            $return->createReturn(null, \enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_WrongNumberofParameters);
                            break;
                        }
                        $URIs[4];
                        $data = $Groups->searchForGroup($URIs[4]);
                        if($data == 301){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST.\enum\returncodes::Error_NoGroupWithSuchName);
                        }
                        else
                        {
                            $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
                        }
                        break;
                    case('Participating'):
                        if(count($URIs) != 4){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_WrongNumberofParameters);
                            break;
                        }
                        $data = $Groups->getEventsForUserWhereUserIsParticipating($UserID);
                        $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
                        break;
                    case('NotParticipatiing'):
                        if(count($URIs) != 4){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_WrongNumberofParameters);
                            break;
                        }
                        $data = $Groups->getEventsForUserWhereUserIsNotParticipating($UserID);
                        $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
                        break;

                }
                break;

            case ('Events'):
                $data = "";
                switch($URIs[3]){
                    case ('Properties'):
                        if(count($URIs) != 5){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data = $Events->getEventProperties($URIs[4]);
                        if($data == 2) {
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_GroupError);
                        }else if($data == 21){
                            $return->createReturn(null,\enum\statuscodes::NOT_FOUND,\enum\returncodes::Error_NoEventWithSuchID);
                        }
                        else{
                            $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
                        }
                        break;
                    case('Groups'):
                        if(count($URIs) != 5){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data = $Events->getGroupsForEvent($URIs[4]);
                        if($data == 7)
                        {
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_QueryError);
                        }
                        else if($data == 22)
                        {
                            $return->createReturn(null,\enum\statuscodes::NOT_FOUND,\enum\returncodes::Error_NoGroupsForThisEvent);
                        }
                        else{
                            $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
                        }
                        break;
                    case('Participants'):
                        if(count($URIs) != 5){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data = $Events->getEventMember($URIs[4]);
                        if($data == 7) {
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_QueryError);
                        }
                        else if($data == 23)
                        {
                            $return->createReturn(null,\enum\statuscodes::NOT_FOUND,\enum\returncodes::Error_NoParticipantsForThisEvent);
                        }
                        else{
                            $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
                        }
                        break;
                    case('Comment'):
                        if(count($URIs) != 5){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_WrongNumberofParameters);
                            break;
                        }
                        $data = $Comments->getCommentsForEvent($URIs[4]);
                        if($data == 51)
                        {
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_NoCommentsForEvent);
                        }
                        else
                        {
                            $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
                            print_r($data);
                        }
                        break;
                }

                break;
            case ('Locations'):
                if(count($URIs) != 3){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                    break;
                }
                $data = $Locations->getAllLocations();
                if($data == 7)
                {
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_QueryError);
                }
                else if($data == 42)
                {
                    $return->createReturn(null,\enum\statuscodes::NOT_FOUND,\enum\returncodes::Error_NoLocationsFound);
                }
                else
                {
                    $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
                }
                break;
            case ('test'):
                //echo 'TEST';
                echo $Users->checkUser('Christopher_Schroth@hotmail.de');
                //hier Testmethoden einfügen
                break;
        }
        break;

    case (\enum\Methods::PUT):
        switch ($URIs[2]) {
            case ("Users"):
                if(count($URIs) != 8) {
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_WrongNumberofParameters);
                }
                elseif($UserID != 312623){
                    $return->createReturn(null,\enum\statuscodes::UNAUTHORIZED,\enum\returncodes::Error_BadPermission);
                }
                else {
                    $data = $Users->newUser($URIs[3], $URIs[4], $URIs[5]);
                    if ($data == 1) {
                        $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_Emailalreadyexits);
                    }
                    elseif($data == 2)
                    {
                        $return->createReturn(null,\enum\statuscodes::INTERNAL_SERVER_ERROR,\enum\returncodes::Error_Emailnotsent);
                    }
                    else {
                        $return->createReturn($data,\enum\statuscodes::CREATED,\enum\returncodes::Success);
                    }
                }
                break;

            case ('Groups'):
                $data = "";
                switch($URIs[3])
                {
                    case('Group'):
                        if(count($URIs) != 8){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data=$Groups->newGroup($URIs[4],$URIs[5],$URIs[6],$URIs[7]);
                        if($data == 0){
                            $return->createReturn(null,\enum\statuscodes::CREATED,\enum\returncodes::Success);
                        }
                        else if($data == 3){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_GroupError);
                        }
                        else if($data == 34){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CouldntAddMember);
                        }
                        else if($data == 33){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantCreateEvent);
                        }
                    break;
                    case('ProtectedGroup'):
                        if(count($URIs) != 7){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data=$Groups->newGroupProtected($URIs[4],$UserID,$URIs[5],$URIs[6]);
                        if($data == 0){
                            $return->createReturn(null,\enum\statuscodes::CREATED,\enum\returncodes::Success);
                        }
                        elseif($data == 3){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_GroupError);
                        }
                        elseif($data == 31){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_WrongGroupPassword);
                        }
                        elseif($data == 34){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CouldntAddMember);
                        }
                    break;
                    case('Member'):
                        if(count($URIs) != 5){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data=$Groups->addMember($URIs[4],$UserID);
                        echo $data;
                        if($data == 0){
                            $return->createReturn(null,\enum\statuscodes::CREATED,\enum\returncodes::Success);
                        }
                        else if($data == 34){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CouldntAddMember);
                        }else if($data == 666){
                            $data = array("Message" => "Group is Protected, please use /Groups/Protected");
                            $return->createReturn($data,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CouldntAddMember);
                        }
                    break;
                    case('Protected'):
                        if(count($URIs) != 6){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data=$Groups->addMemberProtected($URIs[4],$URIs[5],$UserID);
                        if($data == 0){
                           $return->createReturn(null,\enum\statuscodes::CREATED,\enum\returncodes::Success);
                        }else if($data == 31){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_WrongGroupPassword);
                        }else if($data == 34){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CouldntAddMember);
                        }
                    break;
                }
                break;

            case ('Events'):
                switch($URIs[3]){
                    case('Event'):
                        if(count($URIs) != 13){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $Endtime = str_replace('%20', ' ',$URIs[7]);
                        $Starttime =str_replace('%20',' ',$URIs[6]);
                        $data = $Events->newEvent($URIs[4],$URIs[5],$Starttime,$Endtime,$URIs[8],$URIs[9],$UserID,$URIs[10],$URIs[11],$URIs[12]);
                        if($data == 0){
                            $return->createReturn(null,\enum\statuscodes::CREATED,\enum\returncodes::Success);
                        }
                        else if($data == 7){
                            $return->createReturn(null, \enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_QueryError);
                        }
                        else if($data == 25){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantAddParticipant);
                        }
                        else if($data == 201){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_ReachedMaxParticipants);
                        }
                        else if($data == 202){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_ParticipantAlreadyExisting);
                        }
                        break;
                    case('Participant'):
                        if(count($URIs) != 7){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data =$Events->addParticipant($URIs[4],$URIs[5],$URIs[6]);
                        if($data == 0) {
                            $return->createReturn(null,\enum\statuscodes::CREATED,\enum\returncodes::Success);
                        }
                        else if($data == 7){
                            $return->createReturn(null, \enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_QueryError);
                        }
                        else if($data == 25){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantAddParticipant);
                        }
                        else if($data == 201){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_ReachedMaxParticipants);
                        }
                        else if($data == 202){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_ParticipantAlreadyExisting);
                        }
                        break;
                    case('Group'):
                        if(count($URIs) != 6){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data = $Events->addGroup($URIs[4],$URIs[5]);
                        if($data == 0){
                            $return->createReturn(null,\enum\statuscodes::CREATED,\enum\returncodes::Success);
                        }
                        else if($data == 26){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantAddGroupToThisEvent);
                        }
                        else if($data == 203){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_GroupAlreadyAdded);
                        }
                        break;
                    case('Comment'):
                        if(count($URIs) != 6){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_WrongNumberofParameters);
                        }
                        $data = $Comments->newComment($URIs[4],$URIs[5],$UserID);
                        if($data == 50){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantCreateComment);
                        }
                        else{
                            $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                        }
                        break;
                }
                break;

            case ('Locations'):
                if(count($URIs) != 5){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                    break;
                }
                $data = $Locations->newLocation($URIs[3],$URIs[4],$UserID);
                if($data == 0){
                    $return->createReturn(null,\enum\statuscodes::CREATED,\enum\returncodes::Success);
                }
                else if($data == 43){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantCreateLocation);
                }
                break;
        }
        break;

    case (\enum\Methods::POST):
        switch ($URIs[2]) {
            case ("Users"):
                if($main->isValidColumn(\enum\tables\tablenames::User,$URIs[3])) {
                    $code = $Users->setValue($UserID, $URIs[3], $URIs[4]);
                    if ($code) {
                        $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                    } else {
                        $return->createReturn(null,\enum\statuscodes::INTERNAL_SERVER_ERROR,\enum\returncodes::Error_Propertycouldnotbeset);
                    }
                }
                else
                {
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_InvalidTablename);
                }
                break;

            case ('Groups'):
                if(count($URIs) != 6){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                    break;
                }
                if($main->isValidColumn(\enum\tables\tablenames::Group,$URIs[4])){
                    $data = $Groups->setValue($URIs[3],$URIs[4],$URIs[5],$UserID);
                    if($data == 0){
                        $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                    }else if($data == 6){
                        $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_CantSetValue);
                    }else if($data == 30){
                        $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_UserNotGroupOwner);
                    }
                }
                else{
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_InvalidTablename);
                }
                break;

            case ('Events'):
                if(count($URIs) != 6){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                    break;
                }
                if($main->isValidColumn(\enum\tables\tablenames::Event,$URIs[4])){
                    $data = $Events->setValue($URIs[3],$URIs[4],$URIs[5],$UserID);
                    if($data == 0){
                        $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                    }else if($data == 6){
                        $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_CantSetValue);
                    }else if($data == 20){
                        $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_UserNotEventOwner);
                    }
                }
                else{
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_InvalidTablename);
                }
                break;

            case ('Location'):
                if($main->isValidColumn(\enum\tables\tablenames::Location,$URIs[4])){
                    $data = $Locations->changeValue($URIs[3],$URIs[4],$URIs[5],$UserID);
                    if($data == 0){
                        $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                    }else if($data == 6){
                        $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_CantSetValue);
                    }else if($data == 40){
                        $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_UserNotLocationOwner);
                    }
                }
                else{
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_InvalidTablename);
                }
                break;

            case ('Participant'):
                if(count($URIs) != 5){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_WrongNumberofParameters);
                }
                $data = $Events->setPariticipantStatus($URIs[3],$URIs[4],$UserID);
                if($data == 0){
                    $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                }else if($data == 6){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_CantSetValue);
                }

        }
        break;

    case (\enum\Methods::DELETE):
        switch ($URIs[2]) {
            case ("Users"):
                $delete = $Users->deleteUser($URIs[3]);
                switch ($delete){
                    case 0:
                        $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                        break;
                    case 1:
                        $return->createReturn(null,\enum\statuscodes::INTERNAL_SERVER_ERROR,\enum\returncodes::Error_Usercouldnotbedeleted);
                        break;
                    case 2:
                        $return->createReturn(null,\enum\statuscodes::NOT_FOUND,\enum\returncodes::Error_UserDoesnotexist);
                        break;
                    case 3:
                        $return->createReturn(null,\enum\statuscodes::INTERNAL_SERVER_ERROR,\enum\returncodes::Error_CannotDeleteUserFromEvent);
                        break;
                    case 4:
                        $return->createReturn(null,\enum\statuscodes::INTERNAL_SERVER_ERROR,\enum\returncodes::Error_CannotDeleteUserFromGroup);
                        break;
                }
                break;

            case ('Groups'):
                $data = "";
                switch($URIs[3]){
                    case('Group'):
                        if(count($URIs) != 5){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data = $Groups->deleteGroup($URIs[4],$UserID); //Returncodes: 0,30,36,37,39
                        if($data == 0){
                            $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                        }else if($data == 30){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_UserNotGroupOwner);
                        }else if($data == 36){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantDeleteGroup);
                        }else if($data == 37){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantDeleteMember);
                        }else if($data == 39){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantDeleteGroupEvents);
                        }
                        break;
                    case('Member'):
                        if(count($URIs) != 6){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data = $Groups->deleteMember($URIs[4],$URIs[5]); //Returncodes: 0,37
                        if($data == 0){
                            $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                        }else if($data == 37){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantDeleteMember);
                        }else if($data == 36){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantDeleteGroup);
                        }
                        break;
                }

                break;

            case ('Events'):
                $data = "";
                switch($URIs[3]){
                    case('Event'):
                        echo count($URIs);
                        if(count($URIs) != 5){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data = $Events->deleteEvent($URIs[4],$UserID); //Returncodes: 0,20,27,28
                        if($data == 0){
                            $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                        }else if($data == 20){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_UserNotEventOwner);
                        }else if($data == 27){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantDeleteEvent);
                        }else if($data == 28){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantDeleteParticipant);
                        }
                        break;

                    case('Participant'):
                        if(count($URIs) != 6){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data = $Events->deleteParticipant($URIs[4],$URIs[5]);
                        if($data == 0){
                            $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                        }else if($data == 28){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantDeleteParticipant);
                        }
                        break;

                    case('Group'):
                        if(count($URIs) != 6){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data = $Events->removeGroup($URIs[4],$URIs[5]);
                        if($data == 0){
                            $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                        }
                        else if($data == 29){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantDeleteGroupFromEvent);
                        }
                        break;

                    case('Comment'):
                        if(count($URIs) != 5){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_WrongNumberofParameters);
                            break;
                        }
                        $data = $Comments->deleteCommentsForEvent($URIs[4]);
                        if($data == 52){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantDeleteCommentsForEvent);
                        }
                        else{
                            $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                        }
                        break;
                }
                break;

            case ('Location'):
                if(count($URIs) != 4){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                    break;
                }
                $data = $Locations->deleteLocation($URIs[3],$UserID); //Returncodes: 0,40,44
                if($data == 0){
                    $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                }
                else if($data == 40){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_UserNotLocationOwner);
                }
                else if($data == 44){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantDeleteLocation);
                }
                break;
        }
        break;

}