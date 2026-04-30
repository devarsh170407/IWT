<?php
$conn = new mysqli("localhost", "root", "", "P_interiorDB");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT D.DID, C.CategoryName, D.Style, D.Cost, D.Duration, D.PriceCategory 
        FROM DesignCategory D
        JOIN Category C ON D.CatID = C.CatID";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Interior Designs</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; }
        table { border-collapse: collapse; width: 80%; margin: 50px auto; background: white; }
        th, td { padding: 10px; border: 1px solid black; text-align: center; }
        th { background: #333; color: white; }
        h2 { text-align: center; }
    </style>
</head>
<body>

<h2>Interior Design Categories</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Category</th>
        <th>Style</th>
        <th>Cost</th>
        <th>Duration</th>
        <th>Price Category</th>
    </tr>

<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".$row["DID"]."</td>
                <td>".$row["CategoryName"]."</td>
                <td>".$row["Style"]."</td>
                <td>".$row["Cost"]."</td>
                <td>".$row["Duration"]."</td>
                <td>".$row["PriceCategory"]."</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No Data Found</td></tr>";
}
$conn->close();
?>

</table>

</body>
</html>