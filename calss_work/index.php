<table border="solid 2px">
    <tr>
        <th>Sr.No</th>
        <th>Chapter Name</th>
        <th>Page No</th>
    </tr>
    <?php
    for($i =1;$i<6;$i++){
    echo '<tr><td>'.$i.'</td><td>Chapter '.$i.'</td><td>'.($i*$i).'</td></tr>';
    }
    ?>
</table>


