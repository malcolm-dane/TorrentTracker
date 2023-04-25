<?php 
function proper_parse_str($str) {
  # result array
  $arr = array();

  # split on outer delimiter
  $pairs = explode('&', $str);

  # loop through each pair
  foreach ($pairs as $i) {
    # split into name and value
    list($name,$value) = explode('=', $i, 2);
    
    # if name already exists
    if( isset($arr[$name]) ) {
      # stick multiple values into an array
      if( is_array($arr[$name]) ) {
        $arr[$name][] = $value;
      }
      else {
        $arr[$name] = array($arr[$name], $value);
      }
    }
    # otherwise, simply stick it in a scalar
    else {
      $arr[$name] = $value;
    }
  }

  # return result array
  return $arr;
}

// PHP program to append a string 
  
  
// function to append a string 
function append_string ($str1,$str2) {
    $address='https://xchain.newmanagementinc.com/public/';

    // Using Concatenation assignment
    // operator (.=)
    $str1.='.';
      
    // Returning the result 
    return $address .=$str1.= $str2;
}

$query = proper_parse_str($_SERVER['name']['type']);

$file = append_string($query[1,2]);

$head = array_change_key_case(get_headers($file, TRUE));
$size = $head['content-length']; // because filesize() won't work remotely

header('Content-Type: video/mp4');
header('Accept-Ranges: bytes');
header('Content-Disposition: inline');
header('Content-Length:'.$size);

readfile($file);

exit;

?>