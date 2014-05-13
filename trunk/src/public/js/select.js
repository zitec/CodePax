function $_(eleid) {
    return document.getElementById(eleid);
}
window.onload = function() {
    $("#select-active-branches").chosen({disable_search_threshold: 10, search_contains : true});
    $("#select-merged-branches").chosen({disable_search_threshold: 10, search_contains : true});
    $("#select-tags").chosen({disable_search_threshold: 10});

    if ($_("show-merged")) {
        $_("show-merged").style.display = "block";
        $_("hide-merged").style.display = "none";

        $_("show-merged").onclick = function() {
            $_("show-merged").style.display = "none";
            $_("hide-merged").style.display = "block";
        }
        $_("hide-merged").onclick = function() {
            $_("show-merged").style.display = "block";
            $_("hide-merged").style.display = "none";
        }        
    }

    if ($_("show-merged")) {
        $_("show-remote").style.display = "block";
        $_("hide-remote").style.display = "none";

        $_("show-remote").onclick = function() {
            $_("show-remote").style.display = "none";
            $_("hide-remote").style.display = "block";
        }
        $_("hide-remote").onclick = function() {
            $_("show-remote").style.display = "block";
            $_("hide-remote").style.display = "none";
        }
    }
}
