<?php

header('Content-Type: text/xml; charset=utf-8', true);

$showsPath = 'kpodcasts/shows';
$rssPath = 'kpodcasts/rss';
$outputsPath = 'outputs';

$shows = [];
$showFiles = scandir($showsPath);
$entiries = scandir($outputsPath, SCANDIR_SORT_DESCENDING);

foreach ($showFiles as $path) {
    $path = implode('/', [$showsPath, $path]);
    if (!is_file($path)) {
        continue;
    }
    $show = json_decode(file_get_contents($path));
    $show->items = [];
    $shows[basename($path, '.json')] = $show;
}
var_dump($shows);
foreach ($entiries as $path) {
    $path = implode('/', [$outputsPath, $path]);
    if (!is_file($path)) {
        continue;
    }

    $entry = json_decode(file_get_contents($path));
    if (!$data = $entry->data) {
        continue;
    }
    if (array_key_exists($data->sanitized_url, $shows)) {
        if (count($shows[$data->sanitized_url]->items) < 15) {
            $shows[$data->sanitized_url]->items[] = $data;
        } else {
            break;
        }
    }
}
var_dump($shows);
foreach ($shows as $name => $show) {
    $showFolder = implode('/', [$rssPath, $name]);
    if (!count($show->items)) {
        continue;
    }
    
    $firstItem = $show->items[0];
    var_dump($firstItem);
    is_dir($showFolder) or mkdir($showFolder);
    $readmeFile = implode('/', [$showFolder, 'README.md']);
    $rssFile = implode('/', [$showFolder, "$name.xml"]);
    
    $xml = new DOMDocument("1.0", "UTF-8"); // Create new DOM document.
    //create "RSS" element
    $rss = $xml->createElement("rss");
    $rss_node = $xml->appendChild($rss); //add RSS element to XML node
    $rss_node->setAttribute("version", "2.0"); //set RSS version


    //Create RFC822 Date format to comply with RFC822
    $date_f = date("D, d M Y H:i:s T", time());

    $build_date = gmdate(DATE_RFC2822, strtotime($date_f));

    //create "channel" element under "RSS" element
    $channel = $xml->createElement("channel");
    $channel_node = $rss_node->appendChild($channel);

    //add general elements under "channel" node
    $channel_node->appendChild($xml->createElement("title", $firstItem->show_title)); //title
    $channel_node->appendChild($xml->createElement("description", $firstItem->show_title . ' Podcast'));  //description
    $channel_node->appendChild($xml->createElement("link", $firstItem->url_share)); //website link
    $channel_node->appendChild($xml->createElement("language", "tr-tr"));  //language
    $channel_node->appendChild($xml->createElement("copyright", "Copyright Holders"));  //language
    $channel_node->appendChild($xml->createElement("lastBuildDate", $build_date));  //last build date
    $channel_node->appendChild($xml->createElement("generator", "PHP DOMDocument")); //generator
    $channel_node->appendChild($xml->createElement("docs", "http://blogs.law.harvard.edu/tech/rss")); //generator
    foreach ($show->items as $item) {
        $item_node = $channel_node->appendChild($xml->createElement("item")); //create a new node called "item"
        $title_node = $item_node->appendChild($xml->createElement("title", $item->title)); //Add Title under "item"
        $link_node = $item_node->appendChild($xml->createElement("link", $item->url_share)); //add link node under "item"

        //Unique identifier for the item (GUID)
        $guid_link = $xml->createElement("guid", $item->media_url);
        $guid_node = $item_node->appendChild($guid_link);

        //create "description" node under "item"
        $description_node = $item_node->appendChild($xml->createElement("description"));

        //fill description node with CDATA content
        $description_contents = $xml->createCDATASection(htmlentities(transliterator_transliterate('Any-Latin; Latin-ASCII;', $item->subtitle)));
        $description_node->appendChild($description_contents);

        //Published date
        $date_rfc = gmdate(DATE_RFC2822, strtotime($item->release_date));
        $pub_date = $xml->createElement("pubDate", $date_rfc);
        $pub_date_node = $item_node->appendChild($pub_date);

        $enclosure = $item_node->appendChild($xml->createElement("enclosure"));
        $enclosure->setAttribute("url", $item->media_url); //set RSS version
        $enclosure->setAttribute("length", $item->filesize); //set RSS version
        $enclosure->setAttribute("type", "audio/mpeg"); //set RSS version

    }
    file_put_contents($rssFile, $xml->saveXML());

}
