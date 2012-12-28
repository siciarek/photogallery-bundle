var albums = null;
var album = {};
var currentImage = 0;
var images = [];
var albumName = "Album";
var albumDescription = "";
var clickIsDisabled = false;
var frame = 8;

$(document).ready(function () {

    $("div#content").delegate("span.action", "click", function (event) {
        var cls = $(event.target).attr("class").split(" ");
        var action = cls.shift();
        var object = cls.shift();
        var id = parseInt($(event.target).attr("id").replace(/\D+/, ''));

        processAction(action, object, id);
    });

});

function getActionButton(action, object, id) {
    var button = "";
    button += '<span title="' + __(action) + '" class="' + action + ' ' + object + ' action" id="element' + id + '">&nbsp;</span>';

    return button;
}

function getAlbumToolbar(index) {
    var album = albums[index];
    return getAlbumToolbarObj(album);
}

function getAlbumToolbarObj(album) {
    var showHide = album.is_visible === true ? "hide" : "show";

    var toolbar = "";
    toolbar += '<span class="toolbar">';

    toolbar += getActionButton("edit", "album", album.id);
    toolbar += getActionButton(showHide, "album", album.id);
    toolbar += getActionButton("delete", "album", album.id);

    toolbar += '</span>';
    return toolbar;
}

/**
 * http://blog.jbstrickler.com/2011/02/bytes-to-a-human-readable-string/
 * @param size
 * @return {String}
 */
function parseSize(size) {
    var suffix = ["B", "KB", "MB", "GB", "TB", "PB"];
    tier = 0;

    while (size >= 1024) {
        size = size / 1024;
        tier++;
    }

    return Math.round(size * 10) / 10 + " " + suffix[tier];
}

function enableButtons(buttons) {
    buttons.each(function (index, elem) {
        $(elem).button({
            disabled: false
        });
    });
}

function disableButtons(buttons) {
    buttons.each(function (index, elem) {
        $(elem).button({
            disabled: true
        });
    });
}

function getTitle(title, icon) {
    icon = icon || null;
    return icon != null ? '<span class="ui-icon ui-icon-' + icon + '" style="position:relative;background-color:transparent;top:3px;display:inline-block"></span> ' + title : title;
}
