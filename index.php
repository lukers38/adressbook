<?php
//import SQL Utils
include("app/SQLiteConnection.php");
include("app/SQLiteCreateTable.php");
/**
 * Simple Adressbook in PHP
 * (PHP) Task: Build a simple Adressbook where a user can add/edit/delete addressbook entries, sort them by name, phone number, city, etc. Other: Must work on all major operating systems: Linux, OSX and Windows. Its NOT allowed to use ANY framework or other peoples code. A good frontend design is not needed.
 * -index/login Page-
 * @author Lukas Lichtmannecker <lukas.lichtmannecker@stud.hs-bochum.de>
 */

//Init session
session_start();

//Check if user is already logged in, if so redirect to adressbook
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: adressbook.php");
    exit;
}

//Initialize $pdo 
$pdo = (new SQLiteConnection())->connect();
$sqlite_create = new SQLiteCreateTable($pdo);

//Initialize DB if not exist
$sqlite_create->createTable();

//utility variables
$username = $password = "";
$username_err = $password_err = "";

//process data when submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    //check username, if valid store in $username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else{
        $username = trim($_POST["username"]);
    }
    //check password, if valid store in $password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }

    //validate username/password
    if(empty($username_err) && empty($password_err)){
        //prepare sql statement, bind values and fetch results
        $stmt = $pdo->prepare("SELECT user_id, username, password FROM user WHERE username = :username");
        $stmt->bindValue(":username",$username,PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        //if username exists validate password
        if($result){
            $username = $result["username"];
            $hashed_password = $result["password"];
            $id = $result["user_id"];
            if(password_verify($password,$hashed_password)){
                //if password is correct start a new session
                session_start();

                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $id;
                $_SESSION["username"] = $username;

                //redirect
                header("location: adressbook.php");
            } else{
                //password is wrong
                $login_err = "Invalid username or password.";
            }
        } else {
            //user does not exist
            $login_err = "Invalid username or password.";
        }
    }
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="author" content="Lukas Lichtmannecker">
  <title>Login</title>
  <link rel="stylesheet" href="https://v4-alpha.getbootstrap.com/dist/css/bootstrap.min.css">
  <style>
    .wrapper{width: 360px; padding: 20px;}
  </style>

</head>
<body>
  <div class="wrapper">
  <h2>Login</h2>
  <p>Please fill in your credentials to login.</p>
  <?php if(!empty($login_err)){
      echo '<div class="alert alert-danger">' . $login_err . '</div>';
  }?>
  <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
  <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control <?=(!empty($username_err)) ? 'is-invalid' : '';?>">
        <span class="invalid-feedback"><?= $username_err;?></span>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control <?=(!empty($pasword_err)) ? 'is-invalid' : '';?>">
        <span class="invalid-feedback"><?= $password_err;?></span>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Login">
    </div>
    <p>Don't have an account? <a href="register.php"> Register here </a>.</p>
  </form>
  </div>

    <footer style="position:absolute;bottom:0;width:100%;height:50px;background-color: #ddd;">
        <div class="container">
          <p class="text-muted">Author: Lukas Lichtmannecker<br>
          <a class="text-muted" href="mailto:lukas.lichtmannecker@stud.hs-bochum.de">lukas.lichtmannecker@stud.hs-bochum.de</a></p>
    
        </div>
    </footer>
</body>
</html>