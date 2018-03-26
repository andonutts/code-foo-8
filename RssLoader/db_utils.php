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
 * @param string $title        title of the content
 * @param string $desc         description of the content
 * @param string $content_type the content type, e.g. article, video
 * @param string $pub_date_rss publication date, format compliant with RSS 2.0
 * @param string $url          url used to access the content
 * @param string $guid         guid of the content
 */
function addContent(
    string $title, 
    string $desc,
    string $content_type,
    string $pub_date_rss,
    string $url,
    string $guid
) {
    include "db_config.php";

    // convert publication date string to MySQL DATETIME format
    $pub_timestamp = strtotime($pub_date_rss);
    $pub_date_mysql = date("Y-m-d H:i:s", $pub_timestamp);

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }

    // obtain the resource name by removing the directory path from the URL
    $stmt = $conn->prepare(
        "SELECT directory_url FROM content_types
         WHERE content_type_name = ?"
    );

    $stmt->bind_param("s", $content_type);
    $stmt->execute();
    $stmt->bind_result($directory_url);
    $stmt->fetch();
    $stmt->close();

    $resource_name = str_replace($directory_url, "", $url);

    // finally, insert the new content record
    $stmt = $conn->prepare(
        "INSERT INTO content (title, description, content_type_id, pub_date, resource_name, guid)
         VALUES (?, ?,
         (SELECT content_type_id FROM content_types WHERE content_type_name = ? LIMIT 1),
         DATE(?), ?, ?);"
    );
    
    $stmt->bind_param("ssssss", $title, $desc, $content_type, $pub_date_mysql, $resource_name, $guid);
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
 * Removes all content records in the content table.
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
    
    // first, we obtain the content_type_id and resource name
    $stmt = $conn->prepare(
        "SELECT content_type_id, resource_name
         FROM content
         WHERE guid = ?;"
    );
    
    $stmt->bind_param("s", $guid);
    $stmt->execute();
    $stmt->bind_result($content_type_id, $resource_name);
    $stmt->fetch();
    $stmt->close();

    // use the content_type_id to obtain the directory path
    $stmt = $conn->prepare(
        "SELECT directory_url 
         FROM content_types 
         WHERE content_type_id = ?;"
    );

    $stmt->bind_param("s", $content_type_id);
    $stmt->execute();
    $stmt->bind_result($directory_url);
    $stmt->fetch();
    $stmt->close();

    mysqli_close($conn);

    // concatenate the directory path and the resource name to obtain the URL
    return $directory_url . $resource_name;
}

/**
 * Adds a content type record to the 'content_types' table using the provided data.
 * 
 * @param string $content_type_name name of the content type to be added
 * @param string $directory_url    directory path corresponding to the content type
 */
function addContentType(string $content_type_name, string $directory_url) 
{
    include "db_config.php";

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    $stmt = $conn->prepare(
        "INSERT INTO content_types (content_type_name, directory_url)
         VALUES (?, ?);"
    );

    $stmt->bind_param("ss", $content_type_name, $directory_url);
    $stmt->execute();
    $stmt->close();

    mysqli_close($conn);
}

/**
 * Updates the directory path associated with the provided content type name
 * 
 * @param string $content_type_name content type record to be updated with new path
 * @param string $directory_url    new directory path
 */
function updateContentTypeDirectory(string $content_type_name, string $directory_url) 
{
    include "db_config.php";

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn) {
        die("MySQL connection failed: " . mysqli_connect_error());
    }
    
    $stmt = $conn->prepare(
        "UPDATE content_types
         SET directory_url = ?
         WHERE content_type_name = ?;"
    );

    $stmt->bind_param("ss", $directory_url, $content_type_name);
    $stmt->execute();
    $stmt->close();
    
    mysqli_close($conn);
}

?>
