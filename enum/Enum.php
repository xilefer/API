<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.10.2015
 * Time: 15:37
 */

namespace enum;



abstract class Enum{
    private static function getConstants(){
        $ref = new \ReflectionClass(get_called_class());
        return $ref->getConstants();
    }

    public static function isinlist($value){
        return in_array($value,self::getConstants());
    }
}