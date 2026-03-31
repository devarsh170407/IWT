<?php
date_default_timezone_set('Asia/Kolkata');
echo date('d/m/y') . '<br/>';
echo date('d,m,y') . '<br/>';
echo date('D/M/Y') . '<br/>';
echo date('F d, y h:i:s a') . '<br/>';
$timestamp = time();
echo $timestamp . '<br/>';
$dob = mktime(0,0,0,04,17,2007);


echo date("D", $dob) . '<br/>';

$fD = strtotime('+60 days');
echo "Date after 60 days: " . date('d/m/y', $fD) . '<br/>';
echo "Day after 60 days: " . date('l', $fD). '<br/>';

$dob = mktime(0, 0, 0, 4, 17, 2007);

$dob18 = strtotime('+18 years', $dob);

echo "18th Birthday Date: " . date('d/m/Y', $dob18) . '<br/>';
echo "Day: " . date('l', $dob18);

?>