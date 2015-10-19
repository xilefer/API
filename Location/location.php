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
    private $PDO;

    public function __construct()
    {
        $this->database = "applicationdb";
        $this->sqlserver = mysql_pconnect("localhost", "root");
        try{
            $this->PDO = new \PDO("mysql:host=localhost;dbname=applicationdb","root","");
        }
        catch(\PDOException $e){
        echo $e->getMessage();
            exit;
        }

    }

    public function createLocationID()
    {
        $query = "SELECT * FROM Locations WHERE LocationID = :LocationID";
        $stmt = $this->PDO->prepare($query);
        do{
            $rand = rand(0,99999999999);
            $stmt->bindParam(":LocationID",$rand,PDO::PARAM_INT);
            $stmt->execute();
        }
        while($stmt->rowCount() == 1);
    }
    public function newLocation($Name,$Description)
    {
        //Ziehen der LocationID
        $LocationID = $this->createLocationID();
        $query = "INSERT INTO location(`LocationID`,`Name`,`Description`) VALUES (':rand',':Name',':Description')";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(":rand",$LocationID,PDO::PARAM_INT);
        $stmt->bindParam(":Name",$Name,PDO::PARAM_STR);
        $stmt->bindParam(":Description",$Description,PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll();
        //Ab hier noch auf PDO umstellen
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
        $query = "UPDATE location SET Description = $Description WHERE LocationID =:LocationID";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(":LocationID",$LocationID,PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll();
        //$result = mysql_db_query($this->database,$query,$this->sqlserver);

        if($result == FALSE) return 'Error';
        else return 'Successful';
    }
    public function getAllLocations()
    {
        $query = "SELECT Name, Description FROM location";

        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        if($result == FALSE) return 'Error';
        else{
            $result = mysql_fetch_array($result, MYSQL_ASSOC);
            return $result;
        }
    }
}