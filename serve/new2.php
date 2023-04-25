<?php
$remote='https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml';

$ch = curl_init($remote);


$data = curl_exec($ch);
curl_close($ch);

// Load XML file
$xml = simplexml_load_file($data);

// Initialize empty JSON object
$elementNames = array('item');

// Initialize an empty array to store the parsed elements
$elements = array();

// Loop through each element in the XML file
foreach ($xml->children() as $child) {

  // If the element name is in the list of names to parse
  if (in_array($child->getName(), $elementNames)) {

    // Get the element attributes as an array
    $attributes = (array) $child->attributes();

    // Get the element value as a string
    $value = (string) $child;

    // Add the element name, attributes, and value to the array
    $elements[] = array(
      'name' => $child->getName(),
      'attributes' => $attributes,
      'value' => $value
    );
  }
}

// Iterate over each element with a specific name

// Output JSON
echo json_encode($elements);
exit
?>
