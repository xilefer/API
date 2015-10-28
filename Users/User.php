<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 16.10.2015
 * Time: 06:43
 */

namespace Users;

class User{
private $database;
private $sqlserver;
private $PDO;
private $Event;
private $Group;

    public function __construct()
    {
        $this->database = "applicationdb";
        $this->sqlserver = mysql_pconnect("localhost","root");
        $this->PDO = new \PDO('mysql:host=localhost;dbname=applicationdb','root','');
        $this->Event = new \Events\Event();
        $this->Group = new \Groups\group();
    }


    public function getUser($Username)
    {
        $database = $this->database;
        $sqlserver = $this->sqlserver;
        $query = "SELECT * FROM user WHERE Username = '$Username'";
        $this->PDO->query($query);
        $result= mysql_db_query($database, $query, $sqlserver);
        while ($row = mysql_fetch_object($result)) {
            $ID = $row->UserID;
            $Surname = $row->Firstname;
            $User = $row->Username;
            $Lastname = $row->Lastname;
        }
        if(isset($ID))
        {
            $data = array('UserID' => "$ID", 'Benutzername' => "$User", 'Vorname' => "$Surname", 'Nachname' => "$Lastname",'ReturnCode' => '0');
            return $data;
        }
        else
        {
            //$data = array('ReturnCode' => '12');
            return 'Error';
        }
    }

    public function newUser($Username,$Surname,$Lastname,$Password,$Email)
    {
        $database = $this->database;
        $sqlserver = $this->sqlserver;
        if($this->checkUser($Username))
        {
            return 1;
        }
        do{
            $ID = rand(1, 1000000);
            //echo "$ID<br/>";
        }while($this->checkUserID($ID));
        $ActivateToken=rand(1,999999);
        $options = [
            'cost' => 11,
            'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
        ];
        $Crypt = password_hash($Password,PASSWORD_BCRYPT,$options);
        $query = "INSERT INTO `User`(`UserID`, `Username`, `Firstname`, `Lastname`, `Password`, `LoginToken`, `LoginTime`, `Email`,  `Activated`, `ActivateToken` ) VALUES ('$ID','$Username','$Surname','$Lastname','$Crypt','NULL','NULL','$Email','FALSE','$ActivateToken')";
        mysql_db_query($database, $query, $sqlserver);
        $Mail=$this->sendMail($Username);
        $data=$this->getUser($ID);
        if(isset($data->UserID) and $Mail==0)
        {
            return 2;
        }
        elseif(isset($data->UserID) and $Mail==1)
        {
            return 0;
        }
    }


    private function checkUser($Username)
    {
        $database = $this->database;
        $sqlserver = $this->sqlserver;
        $query = "SELECT UserID FROM user WHERE Username = '$Username'";
        $result= mysql_db_query($database, $query, $sqlserver);
        $rowcount=mysql_num_rows($result);

        if($rowcount == 1)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    private function checkUserID($ID)
    {
        $database = $this->database;
        $sqlserver = $this->sqlserver;
        $query = "SELECT Username FROM user WHERE UserID = $ID";
        $result= mysql_db_query($database, $query, $sqlserver);
        $rowcount=mysql_num_rows($result);

        if($rowcount == 1)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public function verifyUser($Username,$Password)
    {
        $PDO = $this->PDO;
        $query = "SELECT `Password` FROM `User` WHERE `Username`='$Username'";
        $Column=$PDO->query($query);
        $PW= $Column->fetchColumn(0);
        //password_verify($Password,$PW)
        //$Password == $PW
        if(password_verify($Password,$PW)){
            return 'Success';
        }
        else{
            return 'Error';
        }




    }

    private function sendMail($Username)
    {
            $PDO = $this->PDO;
            require_once 'C:\xampp\PHPMailer\class.phpmailer.php';
            include 'C:\xampp\PHPMailer\class.smtp.php';
            $query = "SELECT `Email` FROM `user` WHERE `Username`='$Username'";
            $email = $PDO->query($query)->fetchColumn(0);
            $Token = $this->getProperty($Username,'ActivateToken');
            Try{
                $mail = New \PHPMailer();
                $mail->SMTPDebug  = 0;
                $mail->AddAddress("$email");
                $mail->FromName = 'Meet-2-Eat';
                $mail->Subject = "Account Bestätigung";
                $mail->Body = "Herzlich Willkommen bei Meet-2-Eat,</br>"
                    . "Zur Vervollständigung ihrer Registrierung muss ihr Account aktiviert werden.</br>"
                    . "Klicken dazu <a href=https://jacky.hackergrotte.de:86/API/Users/activate/$Username/$Token>hier</a>.";
                $mail->Host = "localhost";
                $mail->Port = 25;
                $mail->IsSMTP();
                $mail->From = "Meet-2-Eat@Mahlzeit.de";
                $mail->isHTML(true);
                $mail->Send();
                return 0;

            }
            catch (phpMailerexception $e){
                return 1;
            }


    }

    private function getProperty($UserID,$Property)
    {
        $PDO=$this->PDO;
        $query = "SELECT `$Property` FROM `user` WHERE `UserID`='$UserID'";
        $Value=$PDO->query($query)->fetchColumn(0);
        return $Value;
    }

    public function activateAccount($Username,$Token)
    {
        $PDO = $this->PDO;

        $UserToken=$this->getProperty($Username,'ActivateToken');
        if($Token==$UserToken) {
            $query = "UPDATE `user` SET `Activated`='TRUE' WHERE `Username`= '$Username'";
            $PDO->query($query);
            $Value = $this->getProperty($Username, 'Activated');
            if ($Value == 'TRUE') {
                return true;
            } else {
                return false;
            }
        }
        else return false;

    }

    public function setValue($UserID,$Table,$Value)
    {

        $PDO=$this->PDO;
        if($Table == \enum\tables\user::Password){
            $options = [
                'cost' => 11,
                'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
            ];
            $Value = password_hash($Value,PASSWORD_BCRYPT,$options);
            //$query = "UPDATE `user` SET `$Table`='$Password' WHERE `UserID`= '$UserID'";
        }
        $query = "UPDATE `user` SET `$Table`='$Value' WHERE `UserID`= '$UserID'";
        $PDO->query($query);
        $check= $this->getProperty($UserID,$Table);
        //echo $Value."\n";
        //echo $check."\n";
        if($check==$Value)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    public function deleteUser($UserID)
    {

        $event = $this->Event;
        $group = $this->Group;
        $PDO= $this->PDO;
        $Namequery = "SELECT `Username` FROM `User` WHERE `UserID`='$UserID'";
        $Username=$PDO->query($Namequery)->fetchColumn(0);
        if(!$Username){
            return 2;
        }
        $query = "DELETE FROM `user` WHERE `UserID`='$UserID'";
        $PDO->query($query);
        $Eventreturn=$event->deleteUserFromEvent($UserID);//0 wenn erfolgreich 1 sonst
        $Groupreturn=$group->deleteUserFromGroup($UserID);//0 wenn erfolgreich 1 sonst
        $check=$this->getUser($Username);
        if($Eventreturn==1){
            return 3;
        }
        if($Groupreturn ==1){
            return 4;
        }
        if($check=='Error' )
        {
            return 0;
        }
        else
        {
            return 1;
        }

    }

    public function getUserID($Username){
        $PDO = $this->PDO;
        $query = "SELECT `UserID` FROM `user` WHERE `Username`= '$Username'";
        $UserID=$PDO->query($query)->fetchColumn(0);
        return $UserID;
    }
}