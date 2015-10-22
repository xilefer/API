<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.10.2015
 * Time: 15:04
 */
namespace enum\tables;

use enum\Enum;

class user extends Enum{

    const ID = 'UserID';
    const Username = 'Username';
    const Firstname = 'Firstname';
    const Lastname = 'Lastname';
    const Password = 'Password';
    const Activated = 'Activated';
    const ActiateToken = 'ActivateToken';
    const Email = 'Email';
    const LoginToken = 'LoginToken';
    const LoginTime = 'LoginTime';
}