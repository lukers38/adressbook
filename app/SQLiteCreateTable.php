<?php
/*
Class SQLiteCreateTable

Initializes the Database
*/
/*
Class SQLiteCreateTable
Offers functions to initially create the adress Table
and to insert a sample entry.
*/
class SQLiteCreateTable{

    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    public function insertSample(){
        $sql = "INSERT INTO adress(first_name,last_name,city,street,phone)
                VALUES('Max','Musterman','Musterstadt','Musterstraße','0123 454545') ";
        $this->pdo->exec($sql);
    }
    
    public function createTable(){
        $sql = "CREATE TABLE IF NOT EXISTS adress (
                adress_id INTEGER PRIMARY KEY,
                first_name TEXT,
                last_name TEXT,
                city TEXT,
                street TEXT,
                phone TEXT,
                edit BOOLEAN DEFAULT 0)";
        $this->pdo->exec($sql);
    }
}
?>