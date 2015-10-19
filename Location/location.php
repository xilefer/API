<?php
/**
 * Created by PhpStorm.
 * User: GeiselhartF
 * Date: 19.10.2015
 * Time: 08:53
 */

namespace Location;


class location
{
    private $database;
    private $sqlserver;

    public function __construct()
    {
        $this->database = "applicationdb";
        $this->sqlserver = mysql_pconnect("localhost", "root");
    }

    public function getLocationID($LocationID)
    {
        $query = "SELECT * FROM Locations WHERE LocationID = $LocationID";
        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        if($result == FALSE) return 'Error';
        else{
            if(mysqli_affected_rows($result) > 0) return TRUE;
            else return FALSE;
        }
    }
    public function newLocation($Name,$Description)
    {
        //Ziehen der LocationID
        do{
            $rand = rand(0,99999999999);
        }while($this->getLocationID($rand) == TRUE);

        $query = "INSERT INTO location(LocationID,Name,Description) VALUES ('$rand','$Name','$Description')";
        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        if($result == FALSE) return 'Error';
        else return 'Successful';
    }
    public function changeName($LocationID, $Name)
    {
        $query = "UPDATE location SET Name = $Name WHERE LocationID = $LocationID";
        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        if($result == FALSE) return 'Error';
        else return 'Successful';
    }
    public function changeDescription($LocationID, $Description)
    {
        $query = "UPDATE location SET Description = $Description WHERE LocationID = $LocationID";
        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        if($result == FALSE) return 'Error';
        else return 'Successful';
    }
    public function getAllLocations()
    {
        $query = "SELECT Name, Description FROM location";
        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        if($result == FALSE) return 'Error';
        else return $result;
    }
}