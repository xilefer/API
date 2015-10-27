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
                $data=$Groups->getGroupsForUser($URIs[3]);
                if($data == 'Error')
                {
                    $return = json_encode(array('ReturnCode' => '32'));
                    $response->setBody($return);
                    $response->setStatuscode(\enum\statuscodes::NOT_FOUND);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application\json');
                    $response->returnResponse();
                }
                else {
                    $json = json_encode($data);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE, 'application\json');
                    $response->setBody("$json");
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->returnResponse();
                }
                break;

            case ('Events'):
                $data = "";
                switch($URIs[3]){
                    case ('Properties'):
                        $data = $Events->getEventProperties($URIs[4]);
                        if($data == 'Error') {
                            $json = json_encode(array('Return Code' => '21'));
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
                    case('Groups'):
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
                $Test = $Events->deleteUserFromEvent(1);
                echo $Test;
                //hier Testmethoden einf�gen
                break;
        }
        break;

    case (\enum\Methods::PUT):
        switch ($URIs[2]) {
            case ("Users"):
                if(count($URIs) != 8) {
                    $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_WrongNumberofParameters);
                }
                else {
                    $data = $Users->newUser($URIs[3], $URIs[4], $URIs[5], $URIs[6], $URIs[7]);
                    if ($data == 1) {
                        $return->createReturn(null,\enum\statuscodes::BAD_REQUEST,\enum\returncodes::Error_Usernamealreadyexits);
                    }
                    elseif($data == 2)
                    {
                        $return->createReturn(null,\enum\statuscodes::INTERNAL_SERVER_ERROR,\enum\returncodes::Error_Emailnotsent);
                    }
                    else {
                        $return->createReturn(null,\enum\statuscodes::CREATED,\enum\returncodes::Success);
                    }
                }
                break;


            case ('Groups'):
                $data = "";
                switch($URIs[3]){
                    case('Group'):
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
                    case('Event'):
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
                if($main->isValidColumn(\enum\tables\tablenames::User,$URIs[4])) {
                    $code = $Users->setValue($URIs[3], $URIs[4], $URIs[5]);
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
                if($delete==0)
                {
                    $return->createReturn(null,\enum\statuscodes::OK,\enum\returncodes::Success);
                }
                elseif($delete==2){
                    $return->createReturn(null,\enum\statuscodes::NOT_FOUND,\enum\returncodes::Error_UserDoesnotexist);
                }
                else{
                    $return->createReturn(null,\enum\statuscodes::INTERNAL_SERVER_ERROR,\enum\returncodes::Error_Usercouldnotbedeleted);
                }
                break;

            case ('Groups'):
                $data = "";
                switch($URIs[3]){
                    case('Group'):
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
                $UserID = $Users->getUserID($Username);
                $data = $Locations->deleteLocation($URIs[3],$UserID);
                if($data == 'Error'){
                    $json = json_encode(array('Return Code' => '44'));
                    $response->setBody($json);
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->returnResponse();
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




