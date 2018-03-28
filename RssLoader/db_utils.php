<?php

namespace Andypasti\RssLoader;

/**
 * Database utility functions
 * 
 * This is a collection of utility functions that define an API for interacting with
 * the database.
 * 
 * PHP version 7.2.3
 */

/**
 * Adds a content record to the 'content' table with the provided data.
 * 
 * @param string $title          title of the content
 * @param string $description    description of the content
 * @param string $pub_date_str   publication date in string format
 * @param string $url            full URL used to access the content
 * @param string $slug           URL valid name of the content
 * @param string $guid           GUID of the content
 * @param string $category       the category, e.g. 'article', 'video'
 * @param string $network        network that produced the content
 * @param string $state          state of the publication, e.g. 'published', 'unpublished'
 */
function addContent(
    string $title,
    string $description,
    string $pub_date_str,
    string $url,
    string $slug,
    string $guid,
    string $category,
    string $network,
    string $state
) {
    include "db_config.php";

    // convert publication date string to MySQL DATETIME format
    $pub_date_timestamp = strtotime($pub_date_str);
    $pub_date_mysql = date("Y-m-d H:i:s", $pub_date_timestamp);

    // obtain the directory URL by removing the resource name (slug) from the URL
    $directory_url = str_replace($slug, "", $url);

    // add the category to the 'categories' table if it does not exist
    addCategory($category, $directory_url);

    // add the network to the 'networks' table if it does not exist
    addNetwork($network);

    // add the state to the 'states' table if it does not exist
    addState($state);

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }

    // finally, insert the new content record
    $stmt = $conn->prepare(
        "INSERT INTO content (title, description, pub_date, slug, guid, category_id, network_id, state_id)
         VALUES (?, ?, DATE(?), ?, ?,
         (SELECT category_id FROM categories WHERE category_name=? LIMIT 1),
         (SELECT network_id FROM networks WHERE network_name=? LIMIT 1),
         (SELECT state_id FROM states WHERE state_name=?));"
    );
    
    $stmt->bind_param("ssssssss", $title, $description, $pub_date_mysql, $slug, $guid, $category, $network, $state);
    $stmt->execute();
    $stmt->close();

    mysqli_close($conn);
}

/**
 * Removes the content record specified by the provided guid.
 * 
 * @param string $guid guid of the content to be removed
 */
function removeContent(string $guid) 
{
    include "db_config.php";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    $stmt = $conn->prepare("DELETE FROM content WHERE guid = ?;");
    
    $stmt->bind_param("s", $guid);
    $stmt->execute();
    $stmt->close();

    mysqli_close($conn);
}

/**
 * Removes all content records in the 'content' table.
 */
function removeAllContent() 
{
    include "db_config.php";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    mysqli_query($conn, "SET SQL_SAFE_UPDATES = 0;");
    mysqli_query($conn, "DELETE FROM content;");
    mysqli_query($conn, "SET SQL_SAFE_UPDATES = 1;");

    mysqli_close($conn);
}

/**
 * Returns the URL of the content record specified by the provided guid.
 * 
 * @param string $guid guid of the content whose URL is to be returned
 */
function getContentUrl(string $guid) 
{
    include "db_config.php";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    // first, we obtain the category_id and slug
    $stmt = $conn->prepare(
        "SELECT category_id, slug
         FROM content
         WHERE guid = ?;"
    );
    
    $stmt->bind_param("s", $guid);
    $stmt->execute();
    $stmt->bind_result($category_id, $slug);
    $stmt->fetch();
    $stmt->close();

    // use the category_id to obtain the directory URL
    $stmt = $conn->prepare(
        "SELECT directory_url 
         FROM categories 
         WHERE category_id = ?;"
    );

    $stmt->bind_param("s", $category_id);
    $stmt->execute();
    $stmt->bind_result($directory_url);
    $stmt->fetch();
    $stmt->close();

    mysqli_close($conn);

    // concatenate the directory URL and the slug to obtain the full URL
    return $directory_url . $slug;
}

/**
 * Adds a record to the 'category' table using the provided data.
 * 
 * @param string $category_name name of the category to be added
 * @param string $directory_url directory URL corresponding to the category
 */
function addCategory(string $category_name, string $directory_url) 
{
    include "db_config.php";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    $stmt = $conn->prepare(
        "INSERT INTO categories (category_name, directory_url)
         VALUES (?, ?);"
    );

    $stmt->bind_param("ss", $category_name, $directory_url);
    $stmt->execute();
    $stmt->close();

    mysqli_close($conn);
}

/**
 * Updates the directory URL associated with the provided category name
 * 
 * @param string $category_name category record to be updated with new URL
 * @param string $directory_url new directory URL
 */
function updateCategoryDirectory(string $category_name, string $directory_url) 
{
    include "db_config.php";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    $stmt = $conn->prepare(
        "UPDATE categories
         SET directory_url = ?
         WHERE category_name = ?;"
    );

    $stmt->bind_param("ss", $directory_url, $category_name);
    $stmt->execute();
    $stmt->close();
    
    mysqli_close($conn);
}

/**
 * Adds a record to the 'network' table using the provided data.
 * 
 * @param string $network_name name of the network, e.g. ign
 */
function addNetwork(string $network_name)
{
    include "db_config.php";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    $stmt = $conn->prepare(
        "INSERT INTO networks (network_name)
         VALUES (?);"
    );

    $stmt->bind_param("s", $network_name);
    $stmt->execute();
    $stmt->close();

    mysqli_close($conn);
}

/**
 * Adds a record to the 'states' table using the provided data.
 * 
 * @param string $state_name name of the state, e.g. published
 */
function addState(string $state_name)
{
    include "db_config.php";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    $stmt = $conn->prepare(
        "INSERT INTO states (state_name)
         VALUES (?);"
    );

    $stmt->bind_param("s", $state_name);
    $stmt->execute();
    $stmt->close();

    mysqli_close($conn);
}

/**
 * Adds a record to the 'content_thumbnail' and 'thumbnails' tables using the provided data.
 * 
 * @param string $guid          GUID of the content that the thumbnail belongs to
 * @param string $thumbnail_url URL of the thumbnail
 * @param string $size_name     description of the image size
 * @param int    $width         thumbnail width in pixels
 * @param int    $height        thumbnail height in pixels
 */
function addContentThumbnail(string $guid, string $thumbnail_url, string $size_name, int $width, int $height)
{
    include "db_config.php";

    // add the thumbnail to the 'thumbnails' table if it does not exist
    addThumbnail($thumbnail_url, $size_name, $width, $height);

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    $stmt = $conn->prepare(
        "INSERT INTO content_thumbnail (content_id, thumbnail_id)
         VALUES ((SELECT content_id FROM content WHERE guid=? LIMIT 1),
         (SELECT thumbnail_id FROM thumbnails WHERE thumbnail_url=? LIMIT 1));"
    );

    $stmt->bind_param("ss", $guid, $thumbnail_url);
    $stmt->execute();
    echo $stmt->error;
    $stmt->close();

    mysqli_close($conn);
}

/**
 * Adds a record to the 'thumbnails' table using the provided data.
 * 
 * @param string $thumbnail_url URL of the thumbnail image
 * @param string $size_name     name of the size, e.g. compact, medium, large
 * @param int    $width         width of the image in pixels
 * @param int    $height        height of the image in pixels
 */
function addThumbnail(string $thumbnail_url, string $size_name, int $width, int $height)
{
    include "db_config.php";

    // add image size record if it does not exist
    addImageSize($size_name, $width, $height);

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    $stmt = $conn->prepare(
        "INSERT INTO thumbnails (thumbnail_url, size_id)
         VALUES (?, (SELECT size_id FROM img_sizes WHERE size_name=? LIMIT 1));"
    );

    $stmt->bind_param("ss", $thumbnail_url, $size_name);
    $stmt->execute();
    echo $stmt->error . "\n";
    $stmt->close();

    mysqli_close($conn);
}

/**
 * Adds a record to the 'img_sizes' table using the provided data.
 * 
 * @param string $size_name name of the size, e.g. compact, medium, large
 * @param int    $width     width in pixels
 * @param int    $height    height in pixels
 */
function addImageSize(string $size_name, int $width, int $height)
{
    include "db_config.php";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    $stmt = $conn->prepare(
        "INSERT INTO img_sizes (size_name, width, height)
         VALUES (?, ?, ?);"
    );

    $stmt->bind_param("sii", $size_name, $width, $height);
    $stmt->execute();
    $stmt->close();

    mysqli_close($conn);
}

/**
 * Adds a record to the 'content_tag' and 'tags' tables using the provided data
 * 
 * @param string $guid     GUID of the content that the tag is associated with
 * @param string $tag_name name of the tag
 */
function addContentTag(string $guid, string $tag_name)
{
    include "db_config.php";

    // add the tag to the 'tags' table if it does not exist
    addTag($tag_name);

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    $stmt = $conn->prepare(
        "INSERT INTO content_tag (content_id, tag_id)
         VALUES ((SELECT content_id FROM content WHERE guid=? LIMIT 1),
         (SELECT tag_id FROM tags WHERE tag_name=? LIMIT 1));"
    );

    $stmt->bind_param("ss", $guid, $tag_name);
    $stmt->execute();
    $stmt->close();

    mysqli_close($conn);
}

/**
 * Adds a record to the 'tags' table using the provided data.
 * 
 * @param string $tag_name name of the tag
 */
function addTag(string $tag_name)
{
    include "db_config.php";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    $stmt = $conn->prepare(
        "INSERT INTO tags (tag_name)
         VALUES (?);"
    );

    $stmt->bind_param("s", $tag_name);
    $stmt->execute();
    $stmt->close();

    mysqli_close($conn);
}

?>
