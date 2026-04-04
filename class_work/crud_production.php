<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'ecom_app_db';

$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}

// INSERT
if(isset($_POST['submit'])){
    $name = $_POST['product_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];

    $img = "";

    if(isset($_FILES['image']) && $_FILES['image']['name'] != ""){
        $img = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "upload/".$img);
    }

    $sql = "INSERT INTO products(product_name,description,price,image) 
            VALUES('$name','$desc','$price','$img')";

    if(mysqli_query($conn, $sql)){
        $message = "Product uploaded successfully";
    } else{
        $message = "Database error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Product CRUD</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

</head>
<body>

<div class="container">
<h2>Product Form</h2>

<form class="form-horizontal" method="post" enctype="multipart/form-data">

<div class="form-group">
<label class="control-label col-sm-2">Product Name:</label>
<div class="col-sm-10">
<input type="text" class="form-control" name="product_name" required>
</div>
</div>

<div class="form-group">
<label class="control-label col-sm-2">Description:</label>
<div class="col-sm-10">
<textarea class="form-control" name="description" required></textarea>
</div>
</div>

<div class="form-group">
<label class="control-label col-sm-2">Price:</label>
<div class="col-sm-10">
<input type="text" class="form-control" name="price" required>
</div>
</div>

<div class="form-group">
<label class="control-label col-sm-2">Product Image:</label>
<div class="col-sm-10">
<input class="form-control" type="file" name="image">
</div>
</div>

<div class="form-group">
<div class="col-sm-offset-2 col-sm-10">
<button type="submit" name="submit" class="btn btn-default">Submit</button>
</div>
</div>

</form>

<hr>

<?php if($message != ""){ ?>
<div class="alert alert-success">
<?php echo $message; ?>
</div>
<?php } ?>

<h3>Product List</h3>

<table class="table table-bordered">
<tr>
<th>ID</th>
<th>Name</th>
<th>Description</th>
<th>Price</th>
<th>Image</th>
</tr>

<?php
$result = mysqli_query($conn, "SELECT * FROM products");

while($row = mysqli_fetch_assoc($result)){
?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['product_name']; ?></td>
<td><?php echo $row['description']; ?></td>
<td><?php echo $row['price']; ?></td>
<td>
<?php if($row['image'] != ""){ ?>
<img src="upload/<?php echo $row['image']; ?>" width="50">
<?php } ?>
</td>
</tr>
<?php } ?>

</table>

</div>

</body>
</html>