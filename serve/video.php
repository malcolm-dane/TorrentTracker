<?php 
$arg = $_GET['name'];
$arg2 = $_GET['type'];
$remote='https://xchain.newmanagementinc.com/public/';
$arg .='.';
$remote.=$arg.=$arg2;
$file = $remote;

$head = array_change_key_case(get_headers($file, TRUE));
$size = $head['content-length']; // because filesize() won't work remotely

header('Content-Type: video/mp4');
header('Accept-Ranges: bytes');
header('Content-Disposition: inline');
header('Content-Length:'.$size);

readfile($file);

exit;

?>