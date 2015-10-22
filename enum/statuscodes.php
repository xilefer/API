<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.10.2015
 * Time: 15:47
 */
namespace enum;

class statuscodes extends Enum{
    const OK = 200;
    const CREATED = 201;
    const UNAUTHORIZED = 401;
    const NOT_FOUND = 404;
    const BAD_REQUEST = 400;
    const INTERNAL_SERVER_ERROR = 500;

}