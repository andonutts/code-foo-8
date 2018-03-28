<?php

namespace Andypasti\RssLoader;

/**
 * Loads RSS data into the database
 * 
 * Uses the built-in PHP extension SimpleXML to load each RSS page, then adds each
 * RSS item to the MySQL database specified in 'db_config.php'. Entries with
 * duplicate GUIDs will not be added due to the UNIQUE constraint applied to the
 * guid column at table creation in 'create_tables.php'.
 * 
 * PHP version 7.2.3
 */

include "db_utils.php";

// iterate through each RSS page
for ($i = 1; $i <= 20; $i++) {
    echo "Adding page $i content\n";

    $rss = simplexml_load_file("https://ign-apis.herokuapp.com/content/feed.rss?page=" . $i);

    if($rss === false) {
        die("Error: Cannot create SimpleXML object");
    }

    $content_list = $rss->channel->item;
    
    // iterate through each RSS item and add it to the database
    foreach($content_list as $content) {
        $ns_ign = $content->children('ign', true);

        addContent(
            $content->title,
            $content->description,
            $content->pubDate,
            $content->link,
            $ns_ign->slug,
            $content->guid,
            $content->category,
            $ns_ign->networks,
            $ns_ign->state
        );

        // add the tags to the database
        $tags = explode(",", $ns_ign->tags);
        foreach($tags as $tag) {
            if($tag != "") {
                addContentTag($content->guid, $tag);
            }
        }
        
        // add the thumbnails to the database
        foreach($ns_ign->thumbnail as $thumbnail) {
            addContentThumbnail(
                $content->guid, 
                $thumbnail->attributes()['link'],
                $thumbnail->attributes()['size'],
                intval($thumbnail->attributes()['width']),
                intval($thumbnail->attributes()['height'])
            );
        }
    }
}

echo "Done";
?>
