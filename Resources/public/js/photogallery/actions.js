function errorHandler(response) {
    $.ui.Mask.hide();
    errorBox(__("Unexpected Exception."));
}

function successHandler(response) {
    var resp = {
        success: false,
        msg: response
    };

    if (typeof response.msg !== "undefined" && typeof response.success !== "undefined") {
        resp = response;
    }

    $.ui.Mask.hide();

    if (resp.success === true) {
        // TODO: no reload action
        infoBox(resp.msg);

//        $.ui.Mask.show(__("Wait a while"));
//        location.reload();
    }
    else {
        errorBox(__(resp.msg));
    }
}


function formAction(form) {
    var p = $(form).parents()[0];
    var element = $(p).attr("id").replace(/^(\w+)\-\w+$/, "$1");
    var messages = {
        album:  "Saving album in progress",
        images: "Saving images in progress"
    };

    $(form).ajaxSubmit({
        error: errorHandler,
        success: successHandler
    });

    $(p).dialog("close");

    $.ui.Mask.show(__(messages[element]));

}

function processAction(action, element, id, message) {

    message = message || "Wait a while";
    var url = null;
    var params = {};

    switch (action) {
        case "edit":

            switch (element) {

                case "album":
                    for (var i = 0; i < albums.length; i++) {
                        var obj = albums[i];
                        if (obj.id == id) {
                            openAlbumForm(__("Edit album data"), obj);
                            break;
                        }
                    }
                    break;

                case "image":
                    for (var i = 0; i < album.length; i++) {
                        if (obj.id == id) {
                            openAlbumForm(__("Edit image data"), obj);
                            break;
                        }
                    }
                    break;
            }

            return;

        case "show":
        case "hide":
            url = Routing.generate("_photogallery_api_show_hide_element", { id: id, action: action, element: element });
            break;

        case "delete":
            url = Routing.generate("_photogallery_api_delete_element", { id: id, element: element });
            confirmDeleteBox(id, element, url);
            return;

        default:
            break;
    }

    $.ui.Mask.show(__(message));
    remoteAction(url, params);
}

function remoteAction(url, params) {

    params = params || {};

    $.ajax({
        url: url,
        data: params,
        error: errorHandler,
        success: successHandler
    });
}

function confirmDeleteBox(id, element, url) {

    var msg = __("Are you sure you want to delete this " + element + "?");

    var yes = __("Delete");
    var no = __("Cancel");
    var dialogTitle = __(title);

    if(element === "image") {
        for(var i = 0; i < album.length; i++) {
            if(album[i].id === id) {
                thumbnail = album[i].thumbnail.src;
                break;
            }
        }
    }

    if(element === "album") {
        for(var i = 0; i < albums.length; i++) {
            if(albums[i].id === id) {
                thumbnail = albums[i].cover.src;
                dialogTitle = albums[i].title;
                break;
            }
        }
    }

    var buttons = {};

    buttons[yes] = function (event) {
        $.ui.Mask.show(__("Deleting in progress"));
        $("#confirmation-dialog").dialog("close");
        $("#image-preview").hide();
        remoteAction(url);
    };

    buttons[no] = function (event) {
        $("#confirmation-dialog").dialog("close");
    };

    $("#confirmation-dialog").dialog({
        title: getTitle(dialogTitle, "trash"),
        dialogClass: "photogallery-form",
        width: 350,
        height: 330,
        closeOnEscape: false,
        draggable: true,
        resizable: false,
        modal: true,
        buttons: buttons,
        open: function () {
            $("#confirmation-dialog #confirmation-message").html(msg);

            $("#confirmation-dialog").css({
                "background": "url(" + thumbnail + ") center no-repeat"
            });

            $(".ui-dialog-titlebar-close").css({
                "display": "none"
            });

            $('.ui-dialog-buttonpane').find('button:contains("' + __("Delete") + '")').button({
                icons: {
                    primary: 'ui-icon-circle-check'
                }
            });

            $('.ui-dialog-buttonpane').find('button:contains("' + __("Cancel") + '")').button({
                icons: {
                    primary: 'ui-icon-circle-close'
                }
            });
        },
        close: function () {

        }
    });
}
