var albums = null;
var currentImage = 0;
var album = [];
var albumName = "Album";
var albumDescription = "";
var clickIsDisabled = false;
var frame = 8;

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
