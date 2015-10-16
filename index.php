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
$URI= $request->getRequestURI();
//echo "      ";
$method= $request->getMethod();
$URIs=explode("/",$URI);
//print_r($URIs);
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
                    $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
                    $response->setBody($json);
                    $response->returnResponse();
                }
                break;


            case ('Groups'):
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
                echo "PostUser$URIs[3]";
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
//$request->getMethod();
}




