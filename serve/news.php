<?php 
 header("Access-Control-Allow-Origin: *");
header("Content-type: text/json;");
$url = "https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml"; // xmld.xml contains above data
$feeds = file_get_contents($url);
$rss = simplexml_load_string($feeds);

$items = [];
$i=0;
foreach($rss->channel->item as $entry) {
    $image = '';
    $image = 'N/A';
    $description = 'N/A';
    foreach ($entry->children('media', true) as $k => $v) {
        $attributes = $v->attributes();

        if ($k == 'content') {
            if (property_exists($attributes, 'url')) {
                $image = $attributes->url;
            }
        }
        if ($k == 'description') {
            $description = $v;
        }
    }

    $items[] = [
       'num'=>$i++,
        'link' => $entry->link,
        'title' => $entry->title,
        'image' => $image,
        'description' => $description,
    ];
}

echo json_encode($rss);
exit
?>