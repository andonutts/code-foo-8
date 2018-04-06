<?php
/**
 * Collects data from IGN's JSON feed and returns it as HTML code
 * 
 * The "contentType" parameter allows the content type to be specified; for example,
 * contentType=article causes the program to return only articles.
 * 
 * PHP version 7.2.3
 */
$start_index = $_REQUEST["startIndex"];
$count = $_REQUEST["count"];
$content_type = $_REQUEST["contentType"];

$current_index = $start_index;
$current_count = 0;

$output_list = array();
$id_str = "";

while ($current_count < $count) {
    // get JSON data
    $content_feed_url = "https://ign-apis.herokuapp.com/content?startIndex=" . $current_index . "&count=10";
    $json_str = file_get_contents($content_feed_url);
    $json_data = json_decode($json_str);
    $content_list = $json_data->{"data"};
    
    // iterate over each content item
    foreach ($content_list as $content) {
        $metadata = $content->{"metadata"};
        $current_index++;

        // if the item is of the desired type, add it to the output list
        if ($metadata->{"contentType"} == $content_type) {
            // collect ID for each content item
            $id_str .= $content->{"contentId"};
            array_push($output_list, $content);
            $current_count++;

            // if the desired amount of content has been found, get the comment count data for each item
            if ($current_count >= $count) {
                $comment_count_url = "https://ign-apis.herokuapp.com/comments?ids=" . $id_str;
                $json_comment_count_str = file_get_contents($comment_count_url);
                $json_comment_count_data = json_decode($json_comment_count_str);
                $comment_counts = $json_comment_count_data->{"content"};

                // build HTML output
                $html_output = "";
                foreach ($output_list as $key => $output_content) {
                    $comment_count = $comment_counts[$key]->{"count"};
                    $metadata = $output_content->{"metadata"};
                    $title = $metadata->{"title"};
                    $slug = $metadata->{"slug"};
                    $link = "http://www.ign.com/" . $content_type . "s/" . $slug;
                    $img_url = $output_content->{"thumbnails"}[0]->{"url"};
                    $age = datetimeToElapsedTime($metadata->{"publishDate"});

                    // if content type is video, get duration of video
                    if ($content_type == "video") {
                        $duration = secondsToMinSec($metadata->{"duration"});
                    }

                    $html_output .= '<div class="content-body">'
                                    .'    <div class="content-thumbnail">'
                                    .'        <a href="' . $link . '">'
                                    .'            <img class="thumbnail-img" src="' . $img_url . '" alt="' . $title . '">';

                    if ($content_type == "video") {
                        $html_output .= '             <div class="img-duration">' . $duration . '</div>';
                    }

                    $html_output .= '        </a>'
                                    .'    </div>'
                                    .'    <div class="content-details">'
                                    .'        <div class="content-metadata">'
                                    .'            <span class="content-age">' . $age . '</span>'
                                    .'            <span class="separator"> - </span>'
                                    .'            <span class="comment-count">' . $comment_count . '</span>'
                                    .'        </div>'
                                    .'        <a href="' . $link . '">'
                                    .'            <h3 class="content-title">' . $title . '</h3>'
                                    .'        </a>'
                                    .'    </div>'
                                    .'</div>';
                }

                // include the current index in the output data so that the receiving application knows where we left off
                $output_data = array(
                    "currentIndex" => $current_index,
                    "html" => $html_output
                );

                // output the content list
                $output_json = json_encode($output_data);
                echo $output_json;
                break;
            }
            $id_str .= ",";
        }
    }
}

/**
 * Converts seconds to string representation of minutes and seconds, formatted as
 * MM:SS.
 * 
 * @param int $sec number of seconds
 * 
 * @return string formatted time string
 */
function secondsToMinSec($sec) 
{
    $m = floor($sec / 60);
    $s = $sec % 60;
    return sprintf("%d:%02d", $m, $s);
}

/**
 * Calculates approximate amount of time elapsed.
 * 
 * @param string $datetime formatted string representation of time
 * 
 * @return string $age approximate time elapsed
 */
function datetimeToElapsedTime($datetime) 
{
    $datetime1 = new DateTime($datetime);
    $datetime2 = new DateTime('now');

    $interval = $datetime1->diff($datetime2);

    if ($interval->y >= 1) {
        $age = $interval->y . "y";
    } else if ($interval->m >= 1) {
        $age = $interval->m . "mo";
    } else if ($interval->d >= 1) {
        $age = $interval->d . "d";
    } else if ($interval->h >= 1) {
        $age = $interval->h . "h";
    } else {
        $age = $interval->i . "m";
    }
    return $age;
}
?>
