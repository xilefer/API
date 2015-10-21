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
                echo "GetGroup$URIs[3]";
                break;

            case ('Events'):
                echo "GetEvent$URIs[3]";
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
                $data=$Groups->newGroup("TestGruppe",$URIs[3],$URIs[4],$URIs[5]);
               if($data == 'Error'){
                   $response->setStatuscode(\enum\statuscodes::BAD_REQUEST);
                   $response->returnResponse();
               }
               else{
                   $json = json_encode($data);
                   $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                   $response->setBody($json);
                   $response->returnResponse();
               }
                echo "PutGroup$URIs[3]";

                break;

            case ('Events'):
                echo "PutEvent$URIs[3]";
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
                echo "PostGroup$URIs[3]";
                break;

            case ('Events'):
                echo "PostEvent$URIs[3]";
                break;

        }
        break;

    case (\enum\Methods::DELETE):
        switch ($URIs[2]) {
            case ("Users"):
                echo "DeleteUser$URIs[3]";
                break;

            case ('Groups'):
                echo "DeleteGroup$URIs[3]";
                break;

            case ('Events'):
                echo "DeleteEvent$URIs[3]";
                break;

        }
        break;
}




