<?php
 if (isset($_REQUEST['btnCheck']))   {
   $sum= 0;
   for($i = 1;$i<5;$i++){
    $sum+=$_REQUEST["m_$i"];
   }
echo $sum." ".($sum/4);
 }else{
    ?>
    <!-- user input -->
 <form method="get">
    <?php for($i = 1;$i<5;$i++){
    echo "<input type='number' name='m_$i'/><br/>";
    }?>
    <button type="submit" name="btnCheck">Check</button>

 </form>
    <?php
 }
?>