<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.10.2015
 * Time: 15:16
 */

include_once 'init.php';

$request = new \request\Request();

echo $request->getRequestURI();
echo "</br>";
$response = new \response\response();

$response->setBody("Hallo");
$response->setStatuscode(\enum\statuscodes::NOT_FOUND);
$response->registerHeader(\enum\Headerfields::CONTENT_LANGUAGE,'de');
$response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');

echo $response->returnResponse();



