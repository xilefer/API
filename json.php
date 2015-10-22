<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.10.2015
 * Time: 16:43
 */

class json {

    public function jsonArray($Key,$Value){
        $data= array() ;
        for($i=0;$i<count($Key);$i++)
        {
            $data[$Key[$i]]=$Value[$i];
        }
        return $data;

    }
}