function defaultOnSuccessCallback(r) {
    // TODO: no reload action
    //  infoBox(r.msg);

    $.ui.Mask.show(__("Wait a while"));
    location.reload();
}

function errorHandler(response) {
    $.ui.Mask.hide();
    errorBox(__("Unexpected Exception."));
}

function successHandler(data, textStatus, jqXHR, onsuccess) {

    onsuccess = typeof onsuccess !== "function"
        ? defaultOnSuccessCallback
        : onsuccess;

    var resp = {
        success: false,
        msg: data
    };

    if (typeof data.msg !== "undefined" && typeof data.success !== "undefined") {
        resp = data;
    }

    $.ui.Mask.hide();

    if (resp.success === true) {
        onsuccess(resp);
    }
    else {
        errorBox(__(resp.msg));
    }
}

function formAction(form) {
    var p = $(form).parents()[0];
    var element = $(p).attr("id").replace(/^(\w+)\-\w+$/, "$1");
    var messages = {
        album: "Saving album in progress",
        images: "Saving images in progress"
    };

    var id = parseInt($(form).find("input[name='id']").val());

    var landingpages = {
        "album" : id > 0 ? location.href : Routing.generate("_albums"),
        "images" : location.href
    };

    var landingpage = landingpages[element];

    $(form).ajaxSubmit({
        error: errorHandler,
        success: function (data, textStatus, jqXHR) {
            var onsuccess = function (data) {
                $.ui.Mask.show(__("Wait a while"));
                location.href = landingpage;
            };

            successHandler(data, textStatus, jqXHR, onsuccess);
        }
    });

    $(p).dialog("close");

    $.ui.Mask.show(__(messages[element]));
}

function processAction(action, element, id, message) {

    message = message || "Wait a while";
    var url = null;
    var params = {};
    var callback = false;

    switch (action) {
        case "move-to":
        case "copy-to":
            var album_id = parseInt(id[0]);
            var image_id = parseInt(id[1]);

            url = Routing.generate("_photogallery_api_copy_image", { album: album_id, image: image_id, action: action });

            if (element === "image") {
                callback =  action === "copy-to"
                ? function (data) { infoBox(data.msg); }
                : function (data) {
                    $.each(images, function (index, element) {
                        if (element != null && element.id === image_id) {
                            var divid = "#img" + index;
                            $(divid).remove();
                            images[index] = null;
                            currentImage = 0;
                            bufferedImage = 1;
                            return;
                        }
                    });
                    infoBox(data.msg);
                };
            }
            else
            {
                return;
            }

            break;

        case "change-cover":
            if (element === "image") {
                url = Routing.generate("_photogallery_api_change_album_cover", { album: album.id, image: id });
                callback = function (data) {
                    $("#album-cover").css({
                        "background-image": "url(" + images[currentImage].thumbnail.src + ")"
                    });
                    album.cover_id = images[currentImage].id;
                };
                message = "Changing album cover";
            }
            break;

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
                    for (var i = 0; i < images.length; i++) {
                        var obj = images[i];
                        console.log(obj);
                        if (obj.id == id) {
                            openImagesForm(__("Edit image data"), obj);
                            break;
                        }
                    }
                    break;
            }

            return;

        case "show":
        case "hide":
            url = Routing.generate("_photogallery_api_show_hide_element", { id: id, action: action, element: element });

            if (element === "image") {
                callback = function (data) {
                    $.each(images, function (index, element) {
                        if (element != null && element.id === id) {
                            var divid = "#img" + index;
                            if (action === "show") {
                                images[index].is_visible = true;
                                $(divid).removeClass("hidden");
                                $(divid).css("width", element.thumbnail.file.width);
                                return;
                            }
                            images[index].is_visible = false;
                            $(divid).addClass("hidden");
                            $(divid).css("width", "216px");
                            if ($(divid).css("background-image") === $("#album-cover").css("background-image")) {
                                $("#album-cover").css("background-image", "url(" + defaultCover + ")");
                            }

                            return;
                        }
                    });
                };
            }

            break;

        case "delete":
            url = Routing.generate("_photogallery_api_delete_element", { id: id, element: element });
            var landingpages = {
                "album": Routing.generate("_albums"),
                "image": location.href
            };

            var landingpage = landingpages[element];

            callback = function (data) {
                $.ui.Mask.show();
                location.href = landingpage;
            };

            if (element === "image") {
                callback = function (data) {
                    $.each(images, function (index, element) {

                        if (element != null && element.id === id) {
                            var divid = "#img" + index;
                            $(divid).remove();
                            images[index] = null;
                            currentImage = 0;
                            bufferedImage = 1;
                            return;
                        }
                    });
                };
            }

            confirmDeleteBox(id, element, url, callback);
            return;

        default:
            break;
    }

    $.ui.Mask.show(__(message));
    remoteAction(url, params, callback);
}

function remoteAction(url, params, callback) {

    params = params || {};
    callback = callback || false;

    var conf = {
        url: url,
        data: params,
        error: errorHandler,
        success: function (data, textStatus, jqXHR) {
            successHandler(data, textStatus, jqXHR, callback);
        }
    };

    $.ajax(conf);
}

function confirmDeleteBox(id, element, url, callback) {

    var msg = __("Are you sure you want to delete this " + element + "?");

    var yes = __("Delete");
    var no = __("Cancel");
    var dialogTitle = __(title);
    var thumbnail = null;

    if (element === "image") {
        for (var i = 0; i < images.length; i++) {
            if (images[i] != null && images[i].id === id) {
                thumbnail = images[i].thumbnail.src;
                break;
            }
        }
    }

    if (element === "album") {
        for (var i = 0; i < albums.length; i++) {
            if (albums[i] != null && albums[i].id === id) {
                thumbnail = albums[i].cover.src;
                dialogTitle = albums[i].title;
                break;
            }
        }
    }

    var buttons = {};

    buttons[yes] = function (event) {
        $.ui.Mask.show(__("Deleting " + element + " in progress"));
        $("#confirmation-dialog").dialog("close");
        $("#image-preview").hide();
        remoteAction(url, {}, callback);
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
