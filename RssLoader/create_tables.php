<?php

namespace Andypasti\RssLoader;

/**
 * Creates MySQL tables
 * 
 * Connects to MySQL using the credentials defined in 'mysql_login.php'. If the
 * connection is successful, the tables 'content' and 'content_types' are 
 * dropped if they already exist, then created. Records for the content types 
 * 'article' and 'video' are then inserted into the 'content_type' table.
 * 
 * PHP version 7.2.3
 */

include "db_config.php";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("MySQL connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "DROP TABLE IF EXISTS content;");
mysqli_query($conn, "DROP TABLE IF EXISTS content_types;");

$querymsg = "CREATE TABLE content_types(
             content_type_id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
             content_type_name VARCHAR(255) UNIQUE NOT NULL,
             directory_url VARCHAR(255) UNIQUE NOT NULL
             );";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Table 'content_types' created successfully\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

$querymsg = "CREATE TABLE content(
             content_id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
             title VARCHAR(255) NOT NULL,
             description VARCHAR(1024) NOT NULL,
             content_type_id INT NOT NULL,
             pub_date DATETIME NOT NULL,
             resource_name VARCHAR(255) NOT NULL,
             guid VARCHAR(255) UNIQUE NOT NULL,
             FOREIGN KEY (content_type_id) REFERENCES content_types(content_type_id)
             );";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Table 'content' created successfully\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

$querymsg = "INSERT INTO content_types (content_type_name, directory_url)
             VALUES ('article', 'http://www.ign.com/articles/');";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Record 'article' added to 'content_types' successfully\n";
} else {
    echo "Error: " . $querymsg . "\n" . mysqli_error($conn);
}

$querymsg = "INSERT INTO content_types (content_type_name, directory_url)
             VALUES ('video', 'http://www.ign.com/videos/');";

if (mysqli_query($conn, $querymsg) === true) {
    echo "Record 'video' added to 'content_types' successfully\n";
} else {
    echo "Error: " . $querymsg . "\n" . mysqli_error($conn);
}

mysqli_close($conn);
?>
