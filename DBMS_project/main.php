<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "P_interiorDB");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// ADD CUSTOMER
if (isset($_POST['add_customer'])) {
    $cid = $_POST['cid'];
    $name = $_POST['name'];
    $city = $_POST['city'];
    $budget = $_POST['budget'];
    $email = $_POST['email'];

    $sql = "INSERT INTO Customer (CID, CName, City, Budget, Email)
            VALUES ('$cid', '$name', '$city', '$budget', '$email')";

    if ($conn->query($sql) === TRUE) {
        $message = "Customer Added Successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// ADD DESIGN CATEGORY
if (isset($_POST['add_design'])) {
    $did = $_POST['did'];
    $catid = $_POST['catid'];
    $style = $_POST['style'];
    $cost = $_POST['cost'];
    $duration = $_POST['duration'];

    // AUTO PRICE CATEGORY
    if ($cost < 40000) {
        $priceCategory = 'Low';
    } elseif ($cost <= 80000) {
        $priceCategory = 'Medium';
    } else {
        $priceCategory = 'Premium';
    }

    $sql = "INSERT INTO DesignCategory (DID, CatID, Style, Cost, Duration, PriceCategory)
            VALUES ('$did', '$catid', '$style', '$cost', '$duration', '$priceCategory')";

    if ($conn->query($sql) === TRUE) {
        $message = "Design Added Successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// FETCH DATA
$result_customer = $conn->query("SELECT * FROM Customer");

$result_design = $conn->query("
    SELECT D.DID, C.CategoryName, D.Style, D.Cost, D.Duration, D.PriceCategory 
    FROM DesignCategory D
    JOIN Category C ON D.CatID = C.CatID
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Interior Management System</title>
    <style>
        body { font-family: Arial; background:#f2f2f2; }
        h2, h3 { text-align:center; }

        .container { width: 90%; margin: auto; }

        .box {
            background:white;
            padding:20px;
            margin:20px 0;
        }

        input {
            padding:8px;
            margin:5px;
        }

        button {
            padding:10px;
            background:#333;
            color:white;
            border:none;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background:white;
            margin-top:20px;
        }

        th, td {
            border:1px solid black;
            padding:8px;
            text-align:center;
        }

        th { background:#333; color:white; }

        .msg { color:green; text-align:center; }
    </style>
</head>
<body>

<div class="container">

<h2>Interior Management System</h2>
<div class="msg"><?php echo $message; ?></div>

<!-- ADD CUSTOMER -->
<div class="box">
<h3>Add Customer</h3>
<form method="POST">
    <input type="number" name="cid" placeholder="CID" required>
    <input type="text" name="name" placeholder="Name" required>
    <input type="text" name="city" placeholder="City" required>
    <input type="number" name="budget" placeholder="Budget" required>
    <input type="email" name="email" placeholder="Email" required>
    <button type="submit" name="add_customer">Add Customer</button>
</form>
</div>

<!-- ADD DESIGN -->
<div class="box">
<h3>Add Design Category</h3>
<form method="POST">
    <input type="number" name="did" placeholder="Design ID" required>
    <input type="number" name="catid" placeholder="Category ID (1=Bedroom,2=Kitchen...)" required>
    <input type="text" name="style" placeholder="Style" required>
    <input type="number" name="cost" placeholder="Cost" required>
    <input type="number" name="duration" placeholder="Duration" required>
    <button type="submit" name="add_design">Add Design</button>
</form>
</div>

<!-- CUSTOMER TABLE -->
<h3>Customer Data</h3>
<table>
<tr>
    <th>CID</th>
    <th>Name</th>
    <th>City</th>
    <th>Budget</th>
    <th>Email</th>
</tr>

<?php
while($row = $result_customer->fetch_assoc()) {
    echo "<tr>
            <td>{$row['CID']}</td>
            <td>{$row['CName']}</td>
            <td>{$row['City']}</td>
            <td>{$row['Budget']}</td>
            <td>{$row['Email']}</td>
          </tr>";
}
?>
</table>

<!-- DESIGN TABLE -->
<h3>Design Categories</h3>
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
while($row = $result_design->fetch_assoc()) {
    echo "<tr>
            <td>{$row['DID']}</td>
            <td>{$row['CategoryName']}</td>
            <td>{$row['Style']}</td>
            <td>{$row['Cost']}</td>
            <td>{$row['Duration']}</td>
            <td>{$row['PriceCategory']}</td>
          </tr>";
}
?>
</table>

</div>

</body>
</html>