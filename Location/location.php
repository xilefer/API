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

    private function createLocationID()
    {
        $PDO = $this->PDO;
        $query = "SELECT * FROM Locations WHERE LocationID = :LocationID";
        $stmt = $PDO->prepare($query);
        do{
            $rand = rand(0,99999999999);
            $stmt->bindParam(":LocationID",$rand,$PDO::PARAM_INT);
            $stmt->execute();
        }
        while($stmt->rowCount() != 0);
        return $rand;
    }
    private function isLocationOwner($OwnerID,$LocationID)
    {
        $query ="SELECT OwnerID FROM location WHERE LocationID = :LocationID";
        $PDO = $this->PDO;
        $stmt=$PDO->prepare($query);
        $stmt->bindParam(":LocationID",$LocationID,$PDO::PARAM_INT);
        if($stmt->execute())
        {
            if($OwnerID == $stmt->fetchColumn()) return TRUE;
            else return FALSE;
        }
        else return 'Error';
    }

    /**
     * Erstellt eine neue Location
     * Returncodes: 0,43
     * @param $Name
     * @param $Description
     * @return string
     */
    public function newLocation($Name,$Description,$OwnerID)
    {
        //Ziehen der LocationID
        $LocationID = $this->createLocationID();
        $query = "INSERT INTO location(`LocationID`,`LocationName`,`Description`,`OwnerID`) VALUES (:LocationID,:NName,:Description,:OwnerID)";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":LocationID",$LocationID,$PDO::PARAM_INT);
        $stmt->bindParam(":NName",$Name,$PDO::PARAM_STR);
        $Description = str_replace('%20',' ',$Description);
        $stmt->bindParam(":Description",$Description,$PDO::PARAM_STR);
        $stmt->bindParam(":OwnerID",$OwnerID,$PDO::PARAM_INT);
        if($stmt->execute()) return 0;
        else return 43;
    }

    /**
     * Löscht eine Location
     * Returncodes: 0,40,44
     * @param $LocationID
     * @param $UserID
     * @return int|string
     */
    public function deleteLocation($LocationID,$UserID)
    {
        if($this->isLocationOwner($UserID,$LocationID))
        {
            $query ="DELETE FROM `location` WHERE LocationID = :LocationID";
            $PDO = $this->PDO;
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":LocationID",$LocationID,$PDO::PARAM_INT);
            if($stmt->execute()) return 0;
            else return 44;
        }
        else return 40;
    }
    public function getAllLocations()
    {
        $query = "SELECT LocationID, LocationName, Description FROM location";
        $PDO = $this->PDO;
        $stmt =$PDO->prepare($query);
        if($stmt->execute())
        {
            if($stmt->rowCount() == 0) return 42;
            return $stmt->fetchAll($PDO::FETCH_ASSOC);
        }
        else{
            return 7;
        }
    }//Index

    /**
     * Setzt einen Wert in der Datenbank
     * Returncodes: 0,6,40
     * @param $LocationID
     * @param $Param
     * @param $Value
     * @param $UserID
     * @return int
     */
    public function changeValue($LocationID,$Param,$Value,$UserID)
    {
        if($this->isLocationOwner($UserID,$LocationID)){
            $query="UPDATE `location` SET :Param = :Value WHERE LocationID = :LocationID";
            $PDO = $this->PDO;
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":Param",$Param,$PDO::PARAM_STR);
            $stmt->bindParam(":Value",$Value,$PDO::PARAM_STR);
            $stmt->bindParam(":LocationID",$LocationID,$PDO::PARAM_INT);
            if($stmt->execute()) return 0;
            else return 6;
        }
        else return 40;
    }
    public function getLocationsWhereUserIsOwner($UserID)
    {
        $PDO = $this->PDO;
        $query = "SELECT LocationID FROM location WHERE OwnerID = :OwnerID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":OwnerID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->fetchAll($PDO::FETCH_COLUMN,0);
        else return 'Error';
    }
    /*
    public function changeName($LocationID, $Name)
    {
        $query = "UPDATE location SET Name = $Name WHERE LocationID = :LocationID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":LocationID",$LocationID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }
    public function changeDescription($LocationID, $Description)
    {
        $query = "UPDATE location SET Description = $Description WHERE LocationID =:LocationID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":LocationID",$LocationID,$PDO::PARAM_INT);
        if( $stmt->execute()) return 'Successful';
        else return 'Error';
    }
    */

}