<?php
/**
 * Searches the database by tags
 * 
 * Returns a list of titles
 */

namespace Andypasti\RssLoader;

require "db_utils.php";

$tag_list = array_slice($argv, 1);
$results = getContentByTags($tag_list);

while ($row = $results->fetch_array(MYSQLI_ASSOC)) {
    printf("%s (%s)\n", $row["title"], $row["link"]);
}

?>
