<?php
$start_index = $_REQUEST["startIndex"];
$count = $_REQUEST["count"];
$content_type = $_REQUEST["contentType"];

// $start_index = 0;
// $count = 5;
// $content_type = "article";

$current_index = $start_index;
$current_count = 0;

$output_list = array();
$id_str = "";

while($current_count < $count) {
    $content_feed_url = "https://ign-apis.herokuapp.com/content?startIndex=" . $current_index . "&count=10";
    $json_str = file_get_contents($content_feed_url);
    $json_data = json_decode($json_str);
    $content_list = $json_data->{"data"};
    
    foreach($content_list as $content) {
        $metadata = $content->{"metadata"};
        $current_index++;
        if($metadata->{"contentType"} == $content_type) {
            $id_str .= $content->{"contentId"};
            array_push($output_list, $content);
            $current_count++;
            if($current_count >= $count) {
                $comment_count_url = "https://ign-apis.herokuapp.com/comments?ids=" . $id_str;
                $json_comment_count_str = file_get_contents($comment_count_url);
                $json_comment_count_data = json_decode($json_comment_count_str);
                $comment_counts = $json_comment_count_data->{"content"};

                $html_output = "";
                foreach($output_list as $key => $output_content) {
                    $comment_count = $comment_counts[$key]->{"count"};
                    $metadata = $output_content->{"metadata"};
                    $title = $metadata->{"title"};
                    $slug = $metadata->{"slug"};
                    $link = "http://www.ign.com/" . $content_type . "s/" . $slug;
                    $img_url = $output_content->{"thumbnails"}[0]->{"url"};
                    $age = datetimeToElapsedTime($metadata->{"publishDate"});
                    if($content_type == "video") {
                        $duration = secondsToMinSec($metadata->{"duration"});
                    }
                    // echo "title: " . $title . "\n";
                    // echo "img URL: " . $img_url . "\n";
                    // echo "comment count: " . $comment_count . "\n";
                    // echo "link: " . $link . "\n";
                    // echo "age: " . $age . "\n";
                    // echo "-----\n";

                    $html_output .= '<div class="content-body">'
                                    .'    <div class="content-thumbnail">'
                                    .'        <a href="' . $link . '">'
                                    .'            <img class="thumbnail-img" src="' . $img_url . '" alt="' . $title . '">';
                    if($content_type == "video") {
                        $html_output .= '             <div class="img-duration">' . $duration . '</div>';
                    }
                    $html_output .= '        </a>'
                                    .'    </div>'
                                    .'    <div class="content-details">'
                                    .'        <div class="content-metadata">'
                                    .'            <span class="content-age">' . $age . '</span>'
                                    .'            <span class="comment-count">' . $comment_count . '</span>'
                                    .'        </div>'
                                    .'        <a href="' . $link . '">'
                                    .'            <h3 class="content-title">' . $title . '</h3>'
                                    .'        </a>'
                                    .'    </div>'
                                    .'</div>';
                }
                $output_data = array(
                    "currentIndex" => $current_index,
                    "html" => $html_output
                );
                $output_json = json_encode($output_data);
                echo $output_json;
                break;
            }
            $id_str .= ",";
        }
    }
}

function msecToAge($msec) {
    $age = "";

    $ageYears = floor(msec / 31536000000);
    $ageDays = floor(msec / 86400000);
    $ageHours = floor(msec / 3600000);
    $ageMinutes = floor(msec / 60000);

    if($ageYears >= 1) {
        $age = $ageYears . "y";
    } else if($ageDays >= 1) {
        $age = $ageDays . "d";
    } else if($ageHours >= 1) {
        $age = $ageHours . "h";
    } else {
        $age = $ageMinutes . "m";
    }
    return $age;
}

function secondsToMinSec($sec) {
    $m = floor($sec / 60);
    $s = $sec % 60;
    return sprintf("%d:%02d", $m, $s);
}

function datetimeToElapsedTime($datetime) {
    $datetime1 = new DateTime($datetime);
    $datetime2 = new DateTime('now');

    $interval = $datetime1->diff($datetime2);

    if($interval->y >= 1) {
        $age = $interval->y . "y";
    } else if($interval->m >= 1) {
        $age = $interval->m . "mo";
    } else if($interval->d >= 1) {
        $age = $interval->d . "d";
    } else if($interval->h >= 1) {
        $age = $interval->h . "h";
    } else {
        $age = $interval->i . "m";
    }
    return $age;
}
?>