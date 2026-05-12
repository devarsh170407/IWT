<?php
$conn = new mysqli("localhost", "root", "");

echo "<h3>databases</h3>";
$res = $conn->query("SHOW DATABASES");
while($row = $res->fetch_assoc()){
    echo $row['Database']."<br>";
}

$conn->select_db("event_db");

echo "<h3>tables</h3>";
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_array()){
    echo $row[0]."<br>";
}

echo "<h3>events table structure</h3>";
$res = $conn->query("DESCRIBE events");
while($row = $res->fetch_assoc()){
    echo $row['Field']." - ".$row['Type']."<br>";
}
?>