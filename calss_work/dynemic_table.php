<form method="post">
    Row<input type="number" name="txtrow" placeholder="Enter rows count"/>
    Column<input type="number" name="txtcol" placeholder="Enter columns count"/>
    <button type="submit" name="btnCreate">Generate</button>
</form>
<?php
    $rows = $_REQUEST['txtrow'];
    $cols = $_REQUEST['txtcol'];
    echo $rows.' '.$cols;
?>