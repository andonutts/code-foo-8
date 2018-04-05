var currentIndex = 0;
var currentType = "video";
window.onload = function() {
    document.getElementById("videos-button").style.backgroundColor = "#dd3a3a";
    document.getElementById("articles-button").style.backgroundColor = "white";
    document.getElementById("videos-button").style.color = "white";
    document.getElementById("articles-button").style.color = "#dd3a3a";
    loadContentPage(currentIndex, currentType);
}

function loadContentPage(index, type) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var jsonData = JSON.parse(xhr.responseText);
            currentIndex = jsonData.currentIndex;
            document.getElementById("content-feed").innerHTML += jsonData.html;
        }
    };

    xhr.open("GET", "feed.php?startIndex=" + index + "&count=5&contentType=" + type, true);
    xhr.send();
}

function toggleVideos() {
    currentIndex = 0;
    currentType = "video";
    document.getElementById("content-feed").innerHTML = "";
    loadContentPage(currentIndex, currentType);
    document.getElementById("videos-button").style.backgroundColor = "#dd3a3a";
    document.getElementById("articles-button").style.backgroundColor = "white";
    document.getElementById("videos-button").style.color = "white";
    document.getElementById("articles-button").style.color = "#dd3a3a";
}

function toggleArticles() {
    currentIndex = 0;
    currentType = "article";
    document.getElementById("content-feed").innerHTML = "";
    loadContentPage(currentIndex, currentType);
    document.getElementById("videos-button").style.backgroundColor = "white";
    document.getElementById("articles-button").style.backgroundColor = "#dd3a3a";
    document.getElementById("videos-button").style.color = "#dd3a3a";
    document.getElementById("articles-button").style.color = "white";
}