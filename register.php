<?php
//import SQL Utils
include("app/SQLiteConnection.php");
include("app/SQLiteCreateTable.php");
/**
 * Simple Adressbook in PHP
 * (PHP) Task: Build a simple Adressbook where a user can add/edit/delete addressbook entries, sort them by name, phone number, city, etc. Other: Must work on all major operating systems: Linux, OSX and Windows. Its NOT allowed to use ANY framework or other peoples code. A good frontend design is not needed.
 * -register Page-
 * @author Lukas Lichtmannecker <lukas.lichtmannecker@stud.hs-bochum.de>
 */

//Initialize $pdo 
$pdo = (new SQLiteConnection())->connect();
$sqlite_create = new SQLiteCreateTable($pdo);

//Initialize DB if not exist
$sqlite_create->createTable();

//utility vars
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";
//debug
$result = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
  //validate username
  if(empty(trim($_POST["username"]))){
    $username_err = "Please enter a username.";
  } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
    $username_err = "Username can only contain letters, numbers and underscores.";
  } else {
    //prepare select statement
    $stmt = $pdo->prepare("SELECT user_id FROM user WHERE username = :username");
    $stmt->bindValue(':username',trim($_POST["username"]), PDO::PARAM_STR);
    //execute statement
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if($result){
      $username_err = "This name is already taken.";
    } else {
      $username = trim($_POST["username"]);
    }
  }
  //validate password
  if(empty(trim($_POST["password"]))){
    $password_err = "Please enter a password.";
  } else{
    $password = trim($_POST["password"]);
  }
  //validate confirm password
  if(empty(trim($_POST["confirm_password"]))){
    $confirm_password_err = "Please confirm your password.";
  } else{
    $confirm_password = trim($_POST["confirm_password"]);
    if(empty($password_err) && ($password != $confirm_password)){
      $confirm_password_err = "Passwords did not match.";
    }
  }
  //doublecheck for errors
  if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
    $stmt = $pdo->prepare("INSERT INTO user(username, password) VALUES (:username,:password)");
    $param_pw = password_hash($password,PASSWORD_DEFAULT);
    if($stmt->execute([
      ":username" => $username,
      ":password" => $param_pw])){
        //redirect to login
        header("location: index.php");
      } else {
        echo "Error executing 'INSERT user' statement.";
      }
  }
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="author" content="Lukas Lichtmannecker">
  <title>Adressbook</title>
  <link rel="stylesheet" href="https://v4-alpha.getbootstrap.com/dist/css/bootstrap.min.css">
  <style>
    .wrapper{width: 360px; padding: 20px;}
  </style>
</head>
<body>
  <div class="wrapper">
    <h2>Register</h2>
    <p>Create an account</p>
    <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" class="form-control <?= (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?= $username; ?>">
      <span class="invalid-feedback"><?= $username_err; ?></span>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" class="form-control <?= (!empty($password_err)) ? 'is-invalid' : '' ; ?>" value="<?= $password;?>">
      <span class="invalid-feedback"><?= $password_err; ?></span>
    </div>
    <div class="form-group">
      <label>Confirm Password</label>
      <input type="password" name="confirm_password" class="form-control <?= (!empty($confirm_password_err)) ? 'is-invalid' : '' ; ?>" value="<?= $confirm_password;?>">
      <span class="invalid-feedback"><?= $confirm_password_err; ?></span>
    </div>
    <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
    </div>
      <p>Already have an account? <a href="index.php">Login here</a>.</p>
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