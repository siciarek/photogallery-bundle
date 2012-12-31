var albums = null;
var album = {};
var currentAlbumId = parseInt(location.href.replace(/^.*\/(\d+)\/[^\/]+$/, "$1"));
var currentImage = 0;
var currentImageInfoElement = null;
var images = [];
var albumName = "Album";
var albumDescription = "";
var clickIsDisabled = false;
var frame = 8;
var imagesInfo = {};
var formIsValid = false;

$(document).ready(function () {

    $("div#content").delegate("span.action", "click", function (event) {
        var cls = $(event.target).attr("class").split(" ");
        var action = cls.shift();
        var object = cls.shift();
        var id = parseInt($(event.target).attr("id").replace(/\D+/, ''));

        processAction(action, object, id);
    });

});

function reorderSequence(elements, collection, cls) {

    var order = [];

    $.ui.Mask.show(__("New " + collection + " sequence is being saved"));

    $(cls).each(function (index, element) {
        if ($(element).attr("id") !== "album-cover") {
            var inx = $(element).attr("id").replace(/[a-z]*/i, '');
            var id = elements[inx].id;
            order.push(id);
        }
    });

    $.ajax({
        url: Routing.generate("_photogallery_api_reorder_sequence", { collection: collection, elements: order.join(",") }),
        error: errorHandler,
        success: function (data, textStatus, jqXHR) {
            var onsuccess = function (data) {
                $("div#menu li#update-view").hide();
                $("div#menu li#undo-changes").hide();

                infoBox(__(data.msg));
            };

            successHandler(data, textStatus, jqXHR, onsuccess);
        }
    });
}

function undoChanges(elem, container) {
    var order = {};
    var count = 0;
    var cover = null;

    $("div#menu li#update-view").hide();
    $("div#menu li#undo-changes").hide();

    $(elem).each(function (index, element) {
        if ($(element).attr("id") === "album-cover") {
            cover = element;
        }
        else {
            var inx = $(element).attr("id").replace(/[a-z]*/i, '');
            order[inx] = element;
            count++;
        }
    });

    $(container).empty();

    if (cover != null) {
        $(container).append(cover);
    }

    for (var i = 0; i < count; i++) {
        var element = order["" + i];

        $(container).append(element);
    }
    $(container).append('<div style="clear:both"></div>');
}

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

    if (authenticated === false) {
        return "";
    }

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

function parseImageTitle(filename) {
    var original_name = filename
    original_name = original_name.replace(/([^\/]+)$/, "$1", original_name);
    original_name = original_name.replace(/\.\w+$/, "", original_name);
    original_name = original_name.replace(/\s+/, " ", original_name);
    original_name = original_name.length == 0 ? "" : original_name;

    return original_name;
}

function getTitle(title, icon) {
    icon = icon || null;
    return icon != null ? '<span class="ui-icon ui-icon-' + icon + '" style="position:relative;background-color:transparent;top:3px;display:inline-block"></span> ' + title : title;
}
