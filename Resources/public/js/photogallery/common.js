var albums = [];
var albumName = "Album";
var albumDescription = "";
var frame = 8;
var clickIsDisabled = false;

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
