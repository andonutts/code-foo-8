<?php
/**
 * Creates MySQL tables
 * 
 * Connects to MySQL using the credentials defined in 'mysql_login.php'. If the
 * connection is successful, the tables are dropped (if they already exist), then
 * created. Once the tables are created, the built-in PHP extension SimpleXML is 
 * used to load each RSS page, then add each RSS item to the MySQL database 
 * specified in 'db_config.php'. Entries with duplicate GUIDs will not be added due
 * to the UNIQUE constraint applied to the guid column at table creation in
 * 'create_tables.php'.
 * 
 * PHP version 7.2.3
 */

namespace Andypasti\RssLoader;

require "db_config.php";
require "db_utils.php";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("MySQL connection failed: " . mysqli_connect_error());
}

echo "Removing previous tables\n";

mysqli_query($conn, "DROP TABLE IF EXISTS content_thumbnail;");
mysqli_query($conn, "DROP TABLE IF EXISTS content_tag;");
mysqli_query($conn, "DROP TABLE IF EXISTS tags;");
mysqli_query($conn, "DROP TABLE IF EXISTS thumbnails;");
mysqli_query($conn, "DROP TABLE IF EXISTS img_sizes;");
mysqli_query($conn, "DROP TABLE IF EXISTS content;");
mysqli_query($conn, "DROP TABLE IF EXISTS states;");
mysqli_query($conn, "DROP TABLE IF EXISTS networks;");
mysqli_query($conn, "DROP TABLE IF EXISTS categories;");

echo "Creating tables\n";

// create 'categories' table
$querymsg = "CREATE TABLE categories(
               category_id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
               category_name VARCHAR(255) UNIQUE NOT NULL,
               directory_url VARCHAR(255) UNIQUE NOT NULL
             );";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Table 'categories' created successfully\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

// create 'networks' table
$querymsg = "CREATE TABLE networks(
               network_id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
               network_name VARCHAR(255) UNIQUE NOT NULL
             );";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Table 'networks' created successfully\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

// create 'states' table
$querymsg = "CREATE TABLE states(
               state_id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
               state_name VARCHAR(255) UNIQUE NOT NULL
             );";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Table 'states' created successfully\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

// create 'content' table 
$querymsg = "CREATE TABLE content(
               content_id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
               title VARCHAR(255) NOT NULL,
               description VARCHAR(2000) NOT NULL,
               pub_date DATETIME NOT NULL,
               link VARCHAR(255) NOT NULL,
               guid VARCHAR(255) UNIQUE NOT NULL,
               category_id INT NOT NULL,
               network_id INT NOT NULL,
               state_id INT NOT NULL,
               FOREIGN KEY (category_id) REFERENCES categories(category_id),
               FOREIGN KEY (network_id) REFERENCES networks(network_id),
               FOREIGN KEY (state_id) REFERENCES states(state_id)
             );";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Table 'content' created successfully\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

// create 'img_sizes' table
$querymsg = "CREATE TABLE img_sizes(
               size_id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
               size_name VARCHAR(255) UNIQUE NOT NULL,
               width INT NOT NULL,
               height INT NOT NULL
             );";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Table 'img_sizes' created successfully\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

// create 'thumbnails' table
$querymsg = "CREATE TABLE thumbnails(
               thumbnail_id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
               thumbnail_url VARCHAR(255) UNIQUE NOT NULL,
               size_id INT NOT NULL,
               FOREIGN KEY (size_id) REFERENCES img_sizes(size_id)
             );";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Table 'thumbnails' created successfully\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

// create 'tags' table
$querymsg = "CREATE TABLE tags(
               tag_id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
               tag_name VARCHAR(255) UNIQUE NOT NULL
             );";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Table 'tags' created successfully\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

// create 'content_tag' table
$querymsg = "CREATE TABLE content_tag(
               content_id INT NOT NULL,
               tag_id INT NOT NULL,
               PRIMARY KEY (content_id, tag_id),
               FOREIGN KEY (content_id) REFERENCES content(content_id),
               FOREIGN KEY (tag_id) REFERENCES tags(tag_id)
             );";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Table 'content_tag' created successfully\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

// create 'content_thumbnail' table
$querymsg = "CREATE TABLE content_thumbnail(
               content_id INT NOT NULL,
               thumbnail_id INT NOT NULL,
               PRIMARY KEY (content_id, thumbnail_id),
               FOREIGN KEY (content_id) REFERENCES content(content_id),
               FOREIGN KEY (thumbnail_id) REFERENCES thumbnails(thumbnail_id)
             );";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Table 'content_thumbnail' created successfully\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);

// iterate through each RSS page
for ($i = 1; $i <= 20; $i++) {
    echo "Adding page $i content\n";

    $rss = simplexml_load_file("https://ign-apis.herokuapp.com/content/feed.rss?page=" . $i);

    if ($rss === false) {
        die("Error: Cannot create SimpleXML object");
    }

    $content_list = $rss->channel->item;
    
    // iterate through each RSS item and add it to the database
    foreach ($content_list as $content) {
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
        foreach ($tags as $tag) {
            if ($tag != "") {
                addContentTag($content->guid, $tag);
            }
        }
        
        // add the thumbnails to the database
        foreach ($ns_ign->thumbnail as $thumbnail) {
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
