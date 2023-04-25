<?php
$remote='https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml';

$ch = curl_init($remote);


$data = curl_exec($ch);
curl_close($ch);

// Load XML file
$xml = simplexml_load_file($data);

// Initialize empty JSON object
$json = new stdClass();

// Iterate over each element with a specific name
foreach ($xml->xpath('//ElementName') as $element) {

    // Add element to JSON object
    $json->elementName[] = (string) $element;
}

// Output JSON
echo json_encode($json);
exit
?>
