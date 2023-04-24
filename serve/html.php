<?php 
$arg = $_GET['name'];
$arg2 = $_GET['type'];
$remote='https://xchain.newmanagementinc.com/public/';
$arg .='.';
$remote.=$arg.=$arg2;
$ch = curl_init($remote);

curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-Encoding: document"));
header('Content-type: html');

$data = curl_exec($ch);

curl_close($ch);

echo $data;

exit;

?>