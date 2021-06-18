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

    public function insertSample($user_id){
        $stmt = $this->pdo->prepare("INSERT INTO adress(first_name,last_name,city,street,phone,user_id)
                VALUES('Max','Musterman','Musterstadt','Musterstraße','0123 454545',:user_id) ");
        //bind user_id 
        $stmt->bindValue(":user_id",$user_id,PDO::PARAM_INT);
        $stmt->execute();
    }
    
    public function createTable(){
        $sql_adress = "CREATE TABLE IF NOT EXISTS adress (
                adress_id INTEGER PRIMARY KEY,
                first_name TEXT,
                last_name TEXT,
                city TEXT,
                street TEXT,
                phone TEXT,
                edit BOOLEAN DEFAULT 0,
                user_id INTEGER,
                FOREIGN KEY(user_id) REFERENCES user(user_id))";
        $sql_users = "CREATE TABLE IF NOT EXISTS user (
            user_id INTEGER PRIMARY KEY,
            username TEXT,
            password TEXT)";
        $this->pdo->exec($sql_users);
        $this->pdo->exec($sql_adress);

    }
}
?>