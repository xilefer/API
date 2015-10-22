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
    $body="ERROR 401 - Unauthorized";
    $response->setBody($body);
    $response->setStatuscode(\enum\statuscodes::UNAUTHORIZED);
    $response->returnResponse();
    exit;
}

if($Auth == 'Error')
{
    $response->setBody("Wrong Username or Password");
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

            case ('Groups'):
                $data=$Groups->getGroupsForUser($URIs[3]);
                if($data == 'Error')
                {
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
                        break;
                    case('Groups'):
                        $data = $Events->getGroupsForEvent($URIs[4]);
                        break;
                    case('Participants'):
                        $data = $Events->isParticipant($URIs[4],$URIs[5]);
                        break;
                }
                if($data != 'Error') {
                    $json = json_encode($data);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->setBody($json);
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->returnResponse();
                }
                else{
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->returnResponse();
                }
                break;
            case ('Location'):
                $data = $Locations->getAllLocations();
                if($data = 'Successful'){
                    $json = json_encode($data);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->setBody($json);
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->returnResponse();
                }
                break;
        }
        break;

    case (\enum\Methods::PUT):
        switch ($URIs[2]) {
            case ("Users"):
                $data=$Users->newUser($URIs[3],$URIs[4],$URIs[5],$URIs[6],$URIs[7]);
                if($data==1)
                {
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->returnResponse();
                }
                else
                {
                    $json = json_encode($data);
                    $response->setStatuscode(\enum\statuscodes::CREATED);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->setBody($json);
                    $response->returnResponse();
                }
                break;


            case ('Groups'):
                $data = "";
                switch($URIs[3]){
                    case('Group'):
                        $data=$Groups->newGroup($URIs[4],$URIs[5],$URIs[6],$URIs[7]);
                        break;
                    case('Member'):
                        $data=$Groups->addMember($URIs[4],$URIs[5]);
                        break;
                }
                if($data == 'Error'){
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->returnResponse();
                }
                else{
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->returnResponse();
                }
                break;

            case ('Events'):
                switch($URIs[3]){
                    case('Event'):
                        $date=$Events->newEvent($URIs[4],$URIs[5],$URIs[6],$URIs[7],$URIs[8],$URIs[9],$URIs[10],$URIs[11],$URIs[12],$URIs[13]);
                        break;
                    case('Participant'):
                        $date =$Events->addParticipant($URIs[4],$URIs[5],$URIs[6]);
                        break;
                    case('Group'):
                        $date = $Events->addGroup($URIs[4],$URIs[5]);
                        break;
                }
                if($data == 'Error'){
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->returnResponse();
                }
                else{
                    $response->setStatuscode(\enum\statuscodes::CREATED);
                    $response->returnResponse();
                }
                echo "PutEvent$URIs[3]";
                break;
            case ('Location'):
                $data = $Locations->newLocation($URIs[3],$URIs[4]);
                if($data = 'Successful'){
                    $response->setStatuscode(\enum\statuscodes::CREATED);
                    $response->returnResponse();
                }
                else{
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->returnResponse();
                }
                break;
        }
        break;

    case (\enum\Methods::POST):
        switch ($URIs[2]) {
            case ("Users"):
                        $code=$Users->setValue($URIs[3],$URIs[4],$URIs[5]);
                        if($code)
                        {
                            $response->setStatuscode(\enum\statuscodes::OK);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }
                        else
                        {
                            $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                            $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                            $response->returnResponse();
                        }


                break;

            case ('Groups'):
                $data = "";
                $data=$Groups->setValue($URIs[3],$URIs[4],$URIs[5],$URIs[6]);//Index
                if($data == 'Successful')
                {
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->returnResponse();
                }
                else
                {
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->returnResponse();
                }
                break;

            case ('Events'):
                $data = $Events->setValue($URIs[3],$URIs[4],$URIs[5],$URIs[6]);
                if($data == 'Successful'){
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->returnResponse();
                }
                else{
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->returnResponse();
                }
                break;
            case ('Location'):
                $data = $Locations->changeValue($URIs[3],$URIs[4],$URIs[5],$URIs[6]);
                if($data = 'Successful'){
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->returnResponse();
                }
                else{
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->returnResponse();
                }
                break;
        }
        break;

    case (\enum\Methods::DELETE):
        switch ($URIs[2]) {
            case ("Users"):
                $Key = Array('Username','UserID','Firstname');
                $Value = Array('Hannelore','1243','Hans');
                $Test = new json();
                $data=$Test->jsonArray($Key,$Value);
                echo json_encode($data);
                //$Users->deleteUser($URIs[3]);
                break;

            case ('Groups'):
                $data = "";
                switch($URIs[3]){
                    case('Group'):
                        $data = $Groups->deleteGroup($URIs[4],$URIs[5]);
                        break;
                    case('Member'):
                        $date = $Groups->deleteMember($URIs[4],$URIs[5]);
                        break;
                }
                if($data == 'Error'){
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

            case ('Events'):
                $data = "";
                switch($URIs[3]){
                    case('Event'):
                        $data = $Events->deleteEvent($URIs[4],$URIs[5]);
                        break;
                    case('Participant'):
                        $data = $Events->deleteParticipant($URIs[4],$URIs[5]);
                        break;
                    case('Group'):
                        $data = $Events->removeGroup($URIs[4],$URIs[5]);
                        break;
                }
                if($data == 'Successful'){
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->returnResponse();
                }
                break;
            case ('Location'):
                $data = $Locations->deleteLocation($URIs[3],$URIs[4]);
                if($data = 'Successful'){
                    $response->setStatuscode(\enum\statuscodes::OK);
                    $response->returnResponse();
                }
                else{
                    $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                    $response->returnResponse();
                }
                break;
        }
        break;
}




