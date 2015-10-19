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
        while($stmt->rowCount() != 0);
        return $rand;
    }
    public function newLocation($Name,$Description)
    {
        //Ziehen der LocationID
        $LocationID = $this->createLocationID();
        $query = "INSERT INTO location(`LocationID`,`Name`,`Description`) VALUES (':LocationID',':Name',':Description')";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(":LocationID",$LocationID,PDO::PARAM_INT);
        $stmt->bindParam(":Name",$Name,PDO::PARAM_STR);
        $stmt->bindParam(":Description",$Description,PDO::PARAM_STR);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }
    public function changeName($LocationID, $Name)
    {
        $query = "UPDATE location SET Name = $Name WHERE LocationID = :LocationID";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(":LocationID",$LocationID,PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }
    public function changeDescription($LocationID, $Description)
    {
        $query = "UPDATE location SET Description = $Description WHERE LocationID =:LocationID";
        $stmt = $this->PDO->prepare($query);
        $stmt->bindParam(":LocationID",$LocationID,PDO::PARAM_INT);
        if( $stmt->execute()) return 'Successful';
        else return 'Error';
    }
    public function getAllLocations()
    {
        $query = "SELECT Name, Description FROM location";
        $stmt =$this->PDO->prepare($query);
        if($stmt->execute())
        {
            return $stmt->fetchAll();
        }
        else{
            return 'Error';
        }
    }
}