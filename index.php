<?php
//import SQL Utils
include("app/SQLiteConnection.php");
include("app/SQLiteCreateTable.php");
/**
 * Simple Adressbook in PHP
 * (PHP) Task: Build a simple Adressbook where a user can add/edit/delete addressbook entries, sort them by name, phone number, city, etc. Other: Must work on all major operating systems: Linux, OSX and Windows. Its NOT allowed to use ANY framework or other peoples code. A good frontend design is not needed.
 * @author Lukas Lichtmannecker <lukas.lichtmannecker@stud.hs-bochum.de>
 */

//Initialize $pdo 
$pdo = (new SQLiteConnection())->connect();
$sqlite_create = new SQLiteCreateTable($pdo);

//Initialize DB if not exist
$sqlite_create->createTable();
//Uncomment next line to insert Sample entry on pageload
//$sqlite_create->insertSample();

//utility vars
$first_name = "";
$last_name = "";
$city = "";
$street = "";
$phone = "";
//sorting util vars
$order_by = "adress_id";
$direction = "ASC";

//handle requests
//edit submitted
//set edit column to 1 so the row gets displayed as editable
if(isset($_POST["edit"])){
  $id = $_POST["adress_id"];
  $stmt = $pdo->prepare("UPDATE adress SET edit = 1 WHERE adress_id = :id");
  $stmt->bindValue(':id',$id,PDO::PARAM_INT);
  $stmt->execute();
}
//delete submitted
elseif(isset($_POST["delete"])){
  $id = $_POST["adress_id"];
  //prepare sql-statement
  $stmt = $pdo->prepare("DELETE FROM adress WHERE adress_id = :id");
  //bind :id to variable $id
  $stmt->bindValue(":id",$id,PDO::PARAM_INT);
  $stmt->execute();
}
//new contact submitted
elseif(isset($_POST["add"])){
  $first_name = $_POST["first_name"];
  $last_name = $_POST["last_name"];
  $city = $_POST["city"];
  $street = $_POST["street"];
  $phone = $_POST["phone"];
  //prepare statement
  $stmt = $pdo->prepare("INSERT INTO adress(first_name,last_name,city,street,phone) VALUES(:first_name,:last_name,:city,:street,:phone)");
  //pass inserted values as array to execute()
  $stmt->execute([
    ":first_name" => $first_name,
    ":last_name" => $last_name,
    ":city" => $city,
    ":street" => $street,
    ":phone" => $phone]);
}
//edited row submitted
elseif(isset($_POST["editsubmit"])){
  $id = $_POST["adress_id"];
  $first_name = $_POST["first_name"];
  $last_name = $_POST["last_name"];
  $city = $_POST["city"];
  $street = $_POST["street"];
  $phone = $_POST["phone"];
  //prep update-statement
  //set edit = 0 so it wont get displayed as editable again
  $stmt = $pdo->prepare("UPDATE adress 
                        SET
                          first_name = :first_name,
                          last_name = :last_name,
                          city = :city,
                          street = :street,
                          phone = :phone,
                          edit = 0
                        WHERE adress_id = :id");
  $stmt->execute([
    ":first_name" => $first_name,
    ":last_name" => $last_name,
    ":city" => $city,
    ":street" => $street,
    ":phone" => $phone,
    ":id" => $id]);
}
//sort submitted
//declares the sorting style
elseif(isset($_POST["orderBy"])){
  $order_by=$_POST["sortBy"];
  if ($_POST["asc"]==0){
    $direction = "DESC";
  }
}
//insert sample
elseif(isset($_POST["sample"])){
  $sqlite_create->insertSample();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="author" content="Lukas Lichtmannecker">
  <title>Adressbook</title>
  <link rel="stylesheet" href="https://v4-alpha.getbootstrap.com/dist/css/bootstrap.min.css">
</head>
<body>
  <h1> Adressbook </h1>
  <h3>Add new contact:</h3>
    <form method="POST" action="">
    <table class="table table-sm" style="width:100%" >
        <tr> 
            <td>First name:</td>
            <td><input class="form-control" type = "text" name = "first_name" value="" required></td>
            <td>Last name:</td>
            <td><input class="form-control" type = "text" name = "last_name" value="" required></td>
        </tr>
        <tr> 
          <td>City:</td>
          <td><input class="form-control" type = "text" name = "city" value="" required></td>
          <td>Street:</td> 
          <td><input class="form-control" type = "text" name = "street" value="" required></td>
          <td>Phone:</td>
          <td><input class="form-control" type = "text" name = "phone" value="" required></td>
        </tr>
        <tr>
          <td> <input class="form-control" type = "reset"></td>
          <td> <input class="form-control" type = "submit" value="Submit" name="add"> </td>
        </tr>
    </table>
    </form>
    <hr/>
    <form class="form-inline" method="POST" action="">
      <label for="sortBy"> Sort table by </label>
      <select class="custom-select" name="sortBy" id="orderBy">
        <option value=adress_id>ID</option>
        <option value=first_name>First name</option>
        <option value=last_name>Last name</option>
        <option value=city>City</option>
        <option value=street>Street</option>
        <option value=phone>Phone</option>
      </select>
      <select class="custom-select" name="asc">
        <option value=1>Ascending</option>
        <option value=0>Descending</option>
      </select>
      <button class="form-control" type="submit" name="orderBy">Sort</button>
    </form>
    <small>Sorted by <?php switch($order_by){
      case "adress_id":
        echo "ID";
        break;
      case "first_name":
        echo "First name";
        break;
      case "last_name":
        echo "Last name";
        break;
      case "city":
        echo "City";
        break;
      case "street":
        echo "Street";
        break;
      case "phone":
        echo "Phone";
        break;
    }
    switch($direction){
      case "ASC":
        echo " ascending.";
        break;
      case "DESC":
        echo " descending.";
        break;
    } //echo " " . $_COOKIE["orderBy"] . " " . $_COOKIE["type"];
    ?> 
    </small>
    <hr/>
    <table class="table table-striped table-sm">
      <thead>
          <th> First name </th>
          <th> Last name </th>
          <th> City </th>
          <th> Street </th>
          <th> Phone </th>
          <th></th>
          <th></th>
      </thead>
      <tbody>
<!-- Fill Table -->
      <?php
        //get all rows ordered from adress table
        $sql = "SELECT * FROM adress ORDER BY " . $order_by . " COLLATE NOCASE " . $direction;
        $rows = $pdo->prepare($sql);
        $rows->execute();

        //cycle through each row and insert the values into a html table row
        //$row : [adress_id,first_name,last_name,city,street,phone,edit]
        //if the row has edit=1, the whole row gets displayed as <input>s
        //else as <td>
        foreach($rows as $row){
          if (!$row['edit']){
            //edit=0
            echo "<tr>\n";
            echo "<td>" . $row['first_name'] . "</td>\n";
            echo "<td>" . $row['last_name'] . "</td>\n";
            echo "<td>" . $row['city'] . "</td>\n";
            echo "<td>" . $row['street'] . "</td>\n";
            echo "<td>" . $row['phone'] . "</td>\n";
            //add edit button
            echo '<td><form method="POST">';         
            echo '<button class="form-control" type="submit" name="edit">Edit</button>';
            echo '<input type="hidden" name="adress_id" value=' . $row['adress_id'] . '></form></td>';
            //add delete button
            echo '<td><form method="POST">';         
            echo '<button class="form-control" type="submit" name="delete">Delete</button>';
            echo '<input type="hidden" name="adress_id" value=' . $row['adress_id'] . '></form></td>';
          } else {
            //edit=1
            echo '<form method="POST"><tr>';
            echo '<td><input class="form-control" type="text" name="first_name" value="' . $row['first_name'] . '" required></td>';
            echo '<td><input class="form-control" type="text" name="last_name" value="' . $row['last_name'] . '" required></td>';
            echo '<td><input class="form-control" type="text" name="city" value="' . $row['city'] . '" required></td>';
            echo '<td><input class="form-control" type="text" name="street" value="' . $row['street'] . '" required></td>';
            echo '<td><input class="form-control" type="text" name="phone" value="' . $row['phone'] . '" required></td>';
            //add edit button        
            echo '<td><button class="form-control" type="submit" name="editsubmit">Submit</button>';
            echo '<input type="hidden" name="adress_id" value=' . $row['adress_id'] . '></form></td>';
            //add delete button
            echo '<td><form method="POST">';         
            echo '<button class="form-control" type="submit" name="delete">Delete</button>';
            echo '<input type="hidden" name="adress_id" value=' . $row['adress_id'] . '></form></td>';
          }
        }
      ?>
      </tbody>
    </table>
    <hr/>
    <form class="form-inline" method="POST" action="">
    <button type="submit" name="sample"> Insert Sample</button>
    </form>
    <footer style="position:absolute;bottom:0;width:100%;height:50px;background-color: #ddd;">
        <div class="container">
          <p class="text-muted">Author: Lukas Lichtmannecker<br>
          <a class="text-muted" href="mailto:lukas.lichtmannecker@stud.hs-bochum.de">lukas.lichtmannecker@stud.hs-bochum.de</a></p>
    
        </div>
      </footer>
</body>
</html>