<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.10.2015
 * Time: 14:56
 */

namespace enum;

class returncodes extends Enum{

    //General Codes
    const Success = 0;
    const General_UserError = 1;
    const General_EventError = 2;
    const General_GroupError = 3;
    const General_LocationError = 4;

    //UserErrorCodes;
    const Error_Emailnotsent = 10;
    const Error_Usernamealreadyexits = 11;
    const Error_UserDoesnotexist = 12;
    const Error_WrongUsernameorPassword = 13;

}