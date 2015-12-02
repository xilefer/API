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

    /**
     * Gibt den Nickname eines Benutzers zurück
     * @param $UserID
     */
    public function getNickname($UserID)
    {
        $PDO = $this->PDO;
        $query = "SELECT `Username` FROM `user` WHERE UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute())
        {
            if($stmt->rowCount() != 0){
                $return = $stmt->fetchColumn();
                return $return;
            }
            else return 102;
        }
        else return 7;
    }
    /**
     * @param $UserID
     * @return array|string
     * Gibt den Benutzernamen des Users zurück
     */
    public function getUser($Email)
    {
        $database = $this->database;
        $sqlserver = $this->sqlserver;
        $query = "SELECT * FROM user WHERE `Email` = '$Email'";
        $this->PDO->query($query);
        $result= mysql_db_query($database, $query, $sqlserver);
        while ($row = mysql_fetch_object($result)) {
            $User = $row->Username;
            $UserID = $row->UserID;
        }
        if(isset($User))
        {
            $data = array('User'=>array('Username' => "$User",'UserID' => "$UserID"));

            return $data;
        }
        else
        {
            //$data = array('ReturnCode' => '12');
            return 'Error';
        }
    }

    /**
     * @param $Email
     * @param $Password
     * @param $Username
     * @return array|int
     * Legt einen neuen Benutzer an
     */
    public function newUser($Email,$Password,$Username)
    {
        $database = $this->database;
        $sqlserver = $this->sqlserver;
        if($this->checkUser($Email))
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
        $query = "INSERT INTO `User`(`UserID`, `Username`, `Password`, `Email`, `Activated`, `ActivateToken` ) VALUES ('$ID','$Username','$Crypt','$Email','FALSE','$ActivateToken')";
        mysql_db_query($database, $query, $sqlserver);
        $Mail=$this->sendMail($Username);
        $data=$this->getNickname($ID);
        if(isset($data['Username']) and $Mail==1)
        {
            return 2;
        }
        elseif(isset($data['Username']) and $Mail==0)
        {
            $return = array('User'=>array('UserID'=>"$ID"));
            return $return;
        }
    }


    /**
     * @param $Email
     * @return bool
     * Prüft ob die Email bereits vorhanden ist
     */
    public function checkUser($Email)
    {
        $database = $this->database;
        $sqlserver = $this->sqlserver;
        $query = "SELECT UserID FROM user WHERE Email = '$Email'";
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

    /**
     * @param $ID
     * @return bool
     * Prüft ob die UserID bereits vergeben ist
     */
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

    /**
     * @param $Email
     * @param $Password
     * @return string
     * Prüft ob die Anmeldedaten korrekt sind
     */
    public function verifyUser($Email,$Password)
    {
        $PDO = $this->PDO;
        $query = "SELECT `Password` FROM `User` WHERE `Email`='$Email'";
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

    /**
     * @param $Username
     * @return int
     * Versendet die Aktivierungsmail an den Benutzer
     */
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

    /**
     * @param $UserID
     * @param $Property
     * @return string
     * Ruft eine bestimmte Eigenschaft eines Benutzers ab
     */
    private function getProperty($UserID,$Property)
    {
        $PDO=$this->PDO;
        $query = "SELECT `$Property` FROM `user` WHERE `UserID`='$UserID'";
        $Value=$PDO->query($query)->fetchColumn(0);
        return $Value;
    }

    /**
     * @param $Username
     * @param $Token
     * @return bool
     * Aktiviert einen Account
     */
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

    /**
     * @param $UserID
     * @param $Table
     * @param $Value
     * @return bool
     * Ändert den Angegeben Wert in der Datenbank
     */
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

    /**
     * @param $UserID
     * @return int
     * Löscht einen Benutzer aus der Datenbank
     */
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
        $check=$this->getNickname($UserID);
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

    /**
     * @param $Email
     * @return string
     * Gibt die UserID zurück
     */
    public function getUserID($Email){
        $PDO = $this->PDO;
        $query = "SELECT `UserID` FROM `user` WHERE `Email`= '$Email'";
        $UserID=$PDO->query($query)->fetchColumn(0);
        return $UserID;
    }

    /**
     * @param $LoginToken
     * @return bool
     * Überprüft das LoginToken auf Gültigkeit
     */
    public function checkLoginToken($LoginToken){
        $PDO=$this->PDO;
        $query = "SELECT `UserID` FROM `user` WHERE `LoginToken`='$LoginToken'";
        $result=$PDO->query($query);
        $Token=$result->fetchColumn(0);
        if(!$Token){
            return false;
        }
        else return true;
    }

    /**
     * @param $Email
     * @return array
     * Funktion zum einloggen eines Benutzers
     */
    public function loginUser($Email){
        $PDO = $this->PDO;
        $checkquery = "SELECT `LoginToken` FROM `user` WHERE `Email`='$Email'";
        $check = $PDO->query($checkquery)->fetchColumn(0);
        if($check == null){
            Do{
                $LoginToken = rand(0,99999999999999999999);
            }while($this->checkLoginToken($LoginToken));

            $query = "UPDATE `user` SET `LoginToken`='$LoginToken',`LoginTime`=CURRENT_TIMESTAMP WHERE `Email`='$Email'";
            $PDO->query($query);
            $selectquery = "SELECT `LoginToken` FROM `user` WHERE `Email`='$Email'";
            $Token=$PDO->query($selectquery)->fetchColumn(0);
            $data = array('LoginToken'=>"$Token");
            return $data;
        }
        else
        {
            $data = array("LoginToken"=>"$check");
            return $data;
        }

    }

    /**
     * @param $Token
     * @return bool|string
     * Überprüft ob das LoginToken zu einem Benutzer gehört und
     * gibt die ID des Benutzers zurück
     */
    public function verifyToken($Token){
        $PDO = $this->PDO;
        $query = "SELECT `UserID` FROM `user` WHERE `LoginToken`= '$Token'";
        $UserID=$PDO->query($query)->fetchColumn(0);
        if($UserID){
            return $UserID;
        }
        else{
            return false;
        }
    }
}