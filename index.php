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
$URI= $request->getRequestURI();
$method= $request->getMethod();
$URIs=explode("/",$URI);
$return = new \methodreturn\createreturn();
$main = new \enum\tables\main();
if(isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW']))
{
    $Username=$_SERVER['PHP_AUTH_USER'];
    $PW= $_SERVER['PHP_AUTH_PW'];
    $Auth=$Users->verifyUser($Username,$PW);
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
    $data = array('ReturnCode' => '14');
    $json=json_encode($data);
    $response->setBody($json);
    $response->setStatuscode(\enum\statuscodes::UNAUTHORIZED);
    $response->registerHeader(\enum\headerfields::CONTENT_TYPE,'application/json');
    $response->returnResponse();
    exit;
}

if($Auth == 'Error')
{
    $data = array('ReturnCode' => '13');
    $json=json_encode($data);
    $response->setBody($json);
    $response->setStatuscode(\enum\statuscodes::UNAUTHORIZED);
    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application\json');
    $response->returnResponse();
    exit;
}
switch ($method) {
    case (\enum\Methods::GET):
        switch ($URIs[2]) {

            case ("Users"):
                $data=$Users->getUser($URIs[3]);
                if($data == 'Error')
                {
                    $return->createReturn(null,\enum\statuscodes::NOT_FOUND,\enum\returncodes::Error_UserDoesnotexist);
                }
                else {
                    $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
                }
                break;

            case ('Groups'):
                if(count($URIs) != 4){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                    break;
                }
                $data=$Groups->getGroupsForUser($URIs[3]);
                if($data == 32){
                    $return->createReturn(null,enum\statuscodes::NOT_FOUND,enum\returncodes::Error_UserHasNoGroups);
                }
                else if($data = 12){
                    $return->createReturn(null,enum\statuscodes::BAD_REQUEST,enum\returncodes::Error_UserDoesnotexist);
                }
                else {
                    $return->createReturn($data,\enum\statuscodes::OK,\enum\returncodes::Success);
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
                            if($data == 'Error') {
                            $json = json_encode(array('Return Code' => '22'));
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->setBody($json);
                            $response->setStatuscode(\enum\statuscodes::NOT_FOUND);
                            $response->returnResponse();
                        }
                        else{
                            $json = json_encode($data);
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::OK);
                            $response->returnResponse();
                        }
                        break;
                    case('Participants'):
                        if(count($URIs) != 5){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data = $Events->getEventMember($URIs[4]);
                        if($data == 'Error') {
                            $json = json_encode(array('Return Code' => '23'));
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->setBody($json);
                            $response->setStatuscode(\enum\statuscodes::NOT_FOUND);
                            $response->returnResponse();
                        }
                        else{
                            $json = json_encode($data);
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::OK);
                            $response->returnResponse();
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
                if($data == 'Error'){
                    $json = json_encode(array('Return Code' => '42'));
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->setBody($json);
                    $response->setStatuscode(\enum\statuscodes::NOT_FOUND);
                    $response->returnResponse();
                }
                else{
                    $json = json_encode($data);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->setBody($json);
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->returnResponse();
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
                if(count($URIs) != 6) {
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_WrongNumberofParameters);
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
                switch($URIs[3]){
                    case('Group'):
                        if(count($URIs) != 8){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data=$Groups->newGroup($URIs[4],$URIs[5],$URIs[6],$URIs[7]);
                        if($data == 'Error'){
                            $json = json_encode(array('Return Code' =>'33'));
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->returnResponse();
                        }else{
                            $json = json_encode($data);
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_LANGUAGE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::OK);
                            $response->returnResponse();
                        }
                        break;
                    case('ProtectedGroup'):
                        if(count($URIs) != 7){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $UserID = $Users->getUserID($Username);
                        $data=$Groups->newGroupProtected($URIs[4],$UserID,$URIs[5],$URIs[6]);
                        if($data == 'Error'){
                            $json = json_encode(array('Return Code' =>'33'));
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->returnResponse();
                        }else{
                            $json = json_encode($data);
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_LANGUAGE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::OK);
                            $response->returnResponse();
                        }

                    case('Member'):
                        if(count($URIs) != 5){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $UserID = $Users->getUserID($Username);
                        $data=$Groups->addMember($URIs[4],$UserID);
                        if($data == 'Error') {
                            $json = json_encode(array('Return Code' => '34'));
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE, 'application/json');
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->returnResponse();
                        }
                        else if($data = 'Group is Protected, please use /Groups/Protected'){
                             $json = json_encode(array('Return Code' => '35'));
                             $response->setBody($json);
                             $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                             $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                             $response->returnResponse();
                        }
                        else{
                            $json = json_encode($data);
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_LANGUAGE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::OK);
                            $response->returnResponse();
                        }
                        break;
                    case('Protected'):
                        if(count($URIs) != 6){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $UserID = $Users->getUserID($Username);
                        $data=$Groups->addMemberProtected($URIs[4],$URIs[5],$UserID);
                        if($data == 'Error'){
                            $json = json_encode(array('Return Code' =>'34'));
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->returnResponse();
                        }else{
                            $json = json_encode($data);
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_LANGUAGE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::OK);
                            $response->returnResponse();
                        }
                }
                break;

            case ('Events'):
                switch($URIs[3]){
                    case('Event')://array_shift($URIs);
                        print_r($URIs);

                                  foreach($URIs as $STR){
                                      $temp = str_replace('%20',' ',$STR);
                                      echo $temp;
                                  }
                        if(count($URIs) != 14){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                                  break;
                        $date=$Events->newEvent($URIs[4],$URIs[5],$URIs[6],$URIs[7],$URIs[8],$URIs[9],$URIs[10],$URIs[11],$URIs[12],$URIs[13]);
                        if($data == 'Error'){
                            $json = json_encode(array('Return Code' => '24'));
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->returnResponse();
                        }else if($data = 'ErrorParticipant'){
                            $json = json_encode(array('Return Code' => '25'));
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->returnResponse();
                        }
                        else{
                            $response->setStatuscode(\enum\statuscodes::CREATED);
                            $response->returnResponse();
                        }
                        break;
                    case('Participant'):
                        if(count($URIs) != 7){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $date =$Events->addParticipant($URIs[4],$URIs[5],$URIs[6]);
                        if($data == 'Error'){
                            $json = json_encode(array('Return Code' => '25'));
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->returnResponse();
                        }
                        else{
                            $response->setStatuscode(\enum\statuscodes::CREATED);
                            $response->returnResponse();
                        }
                        break;
                    case('Group'):
                        if(count($URIs) != 6){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $date = $Events->addGroup($URIs[4],$URIs[5]);
                        if($data == 'Error'){
                            $json = json_encode(array('Return Code' => '26'));
                            $response->setBody($json);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->returnResponse();
                        }
                        else{
                            $response->setStatuscode(\enum\statuscodes::CREATED);
                            $response->returnResponse();
                        }
                        break;
                }

                break;
            case ('Locations'):
                if(count($URIs) != 5){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                    break;
                }
                $data = $Locations->newLocation($URIs[3],$URIs[4]);
                if($data == 'Error'){
                    $json = json_encode(array('Return Code' => '43'));
                    $response->setBody($json);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->returnResponse();
                }
                else{
                    $response->setStatuscode(\enum\statuscodes::OK);

                    $response->returnResponse();
                }
                break;
        }
        break;

    case (\enum\Methods::POST):

        switch ($URIs[2]) {
            case ("Users"):
                $UserID=$Users->getUserID($Username);
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
                $data = "";
                $UserID = $Users->getUserID($Username);

                $data=$Groups->setValue($URIs[3],$URIs[4],$URIs[5],$UserID);
                if($data == 'Error')
                {
                    $json = json_encode(array('Return Code' => '6'));
                    $response->setBody($json);
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->returnResponse();
                }
                else
                {
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->returnResponse();
                }
                break;

            case ('Events'):
                if(count($URIs) != 6){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                    break;
                }
                $UserID = $Users->getUserID($Username);
                $data = $Events->setValue($URIs[3],$URIs[4],$URIs[5],$UserID);
                if($data == 'Error'){
                    $json = json_encode(array('Return Code' => '6'));
                    $response->setBody($json);
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->returnResponse();
                }
                else{
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->returnResponse();
                }
                break;
            case ('Location'):
                $UserID = $Users->getUserID($Username);
                $data = $Locations->changeValue($URIs[3],$URIs[4],$URIs[5],$UserID);
                if($data = 'Error'){
                    $json = json_encode(array('Return Code' => '6'));
                    $response->setBody($json);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->returnResponse();
                }
                else{
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->returnResponse();
                }
                break;
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
                        $UserID = $Users->getUserID($Username);
                        $data = $Groups->deleteGroup($URIs[4],$UserID);
                        if($data == 'Error'){
                            $json = json_encode(array('Return Code' => '36'));
                            $response->setBody($json);
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }else if($data = 'User is no Admin'){
                            $json = json_encode(array('Return Code' => '30'));
                            $response->setBody($json);
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }
                        else{
                            $response->setStatuscode(\enum\statuscodes::OK);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }
                        break;
                    case('Member'):
                        if(count($URIs) != 6){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $date = $Groups->deleteMember($URIs[4],$URIs[5]);
                        if($data == 'Error'){
                            $json = json_encode(array('Return Code' => '37'));
                            $response->setBody($json);
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }
                        else{
                            $response->setStatuscode(\enum\statuscodes::OK);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }
                        break;
                }

                break;

            case ('Events'):
                $data = "";
                switch($URIs[3]){
                    case('Event'):
                        if(count($URIs) != 5){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $UserID = $Users->getUserID($Username);
                        $data = $Events->deleteEvent($URIs[4],$UserID);
                        if($data == 'Error'){
                            $json = json_encode(array('Return Code' => '27'));
                            $response->setBody($json);
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }else if($data = 'UserNotEventAdmin'){
                            $json = json_encode(array('Return Code' => '20'));
                            $response->setBody($json);
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }
                        else{
                            $response->setStatuscode(\enum\statuscodes::OK);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }
                        break;
                    case('Participant'):
                        if(count($URIs) != 6){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data = $Events->deleteParticipant($URIs[4],$URIs[5]);
                        if($data == 'Error'){
                            $json = json_encode(array('Return Code' => '28'));
                            $response->setBody($json);
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }
                        else{
                            $response->setStatuscode(\enum\statuscodes::OK);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }
                        break;
                    case('Group'):
                        if(count($URIs) != 6){
                            $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                            break;
                        }
                        $data = $Events->removeGroup($URIs[4],$URIs[5]);
                        if($data == 'Error'){
                            $json = json_encode(array('Return Code' => '29'));
                            $response->setBody($json);
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }
                        else{
                            $response->setStatuscode(\enum\statuscodes::OK);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }
                        break;
                }
                break;
            case ('Location'):
                if(count($URIs) != 4){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::General_WrongNumberOfParameter);
                    break;
                }
                $UserID = $Users->getUserID($Username);
                $data = $Locations->deleteLocation($URIs[3],$UserID);
                if($data == 'Error'){
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_CantDeleteLocation); // 44
                }else if($data = 'User is not Locationowner'){
                    $json = json_encode(array('Return Code' => '40'));
                    $response->setBody($json);
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->returnResponse();
                }
                else{
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->returnResponse();
                }
                break;
        }
        break;

}




