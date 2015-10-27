<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 27.10.2015
 * Time: 16:23
 */
namespace methodreturn;

class createreturn {

    private $response;

    public function __construct(){
        $this->response = new \response\response();
    }

    public function createReturn($data=null,$status,$returncode) {

        if(!isset($data)){
            $data = array();
        }
        $response = $this->response;
        $response->registerHeader(\enum\Headerfields::CONTENT_TYPE,'application/json');
        if($returncode==0){
            $data['Returncode']='0';
            $response->setStatuscode($status);
        }
        else{
            $data['Returncode']= $returncode;
            $response->setStatuscode($status);
        }
        $json=json_encode($data);
        $response->setBody($json);
        $response->returnResponse();
    }
}