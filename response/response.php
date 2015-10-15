<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.10.2015
 * Time: 16:32
 */
namespace response;

    class response{
        private $body = 'hallo';
        private $statuscode = \enum\statuscodes::OK;
        private $reponseHeaders = array();

        public function setBody($body) {
            $this->body = $body;
            
        }

        public function setStatuscode($code) {
            $this->statuscode = $code;
        }

        public function registerHeader($header,$value) {
            $this->reponseHeaders[$header] = $value ;
        }

        public function returnResponse() {
            http_response_code($this->statuscode);
            foreach($this->reponseHeaders as $headerfield => $value){
                header($headerfield.':'.$value);
            }
            return $this->body;

        }

    }
