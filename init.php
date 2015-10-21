<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.10.2015
 * Time: 15:14
 */

function autoload($classname)
{
    if($classname!= 'PHPMailer')
    {
        include_once implode(DIRECTORY_SEPARATOR, explode('\'',$classname)).'.php';
    }

}
spl_autoload_register('autoload');
