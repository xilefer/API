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

    public function __construct()
    {
        $this->database = "applicationdb";
        $this->sqlserver = mysql_pconnect("localhost","root");
        $this->PDO = new \PDO('mysql:host=localhost;dbname=applicationdb','root','');
    }


    public function getUser($UserID)
    {
        $database = $this->database;
        $sqlserver = $this->sqlserver;
        $query = "SELECT * FROM user WHERE UserID = '$UserID'";
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
            $data = array('UserID' => "$ID", 'Benutzername' => "$User", 'Vorname' => "$Surname", 'Nachname' => "$Lastname");
            return $data;
        }
        else
        {
            return 'Error';
        }
    }

    public function newUser($Username,$Surname,$Lastname,$Password,$Email)
    {
        $database = $this->database;
        $sqlserver = $this->sqlserver;
        if($this->checkUser($Username))
        {
            return '1';
        }
        do{
            $ID = rand(1, 1000000);
            //echo "$ID<br/>";
        }while($this->checkUserID($ID));
        echo $ID;
        $query = "INSERT INTO `User`(`UserID`, `Username`, `Firstname`, `Lastname`, `Password`, `LoginToken`, `LoginTime`, `Email`,  `Activated`) VALUES ('$ID','$Username','$Surname','$Lastname','$Password','NULL','NULL','$Email','FALSE')";
        mysql_db_query($database, $query, $sqlserver);
        //echo "Successfully created User with the ID $ID<br/>";
        $data=$this->getUser($ID);
        return $data;
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
        if($PW == $Password){
            return 'Success';
        }
        else{
            return 'Error';
        }




    }
}