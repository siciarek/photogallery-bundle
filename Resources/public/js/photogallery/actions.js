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
        "album": id > 0 ? location.href : Routing.generate("_albums"),
        "images": location.href
    };

    var landingpage = landingpages[element];

    $(form).ajaxSubmit({
        error: errorHandler,
        success: function (data, textStatus, jqXHR) {
            var onsuccess = function (response) {
                $.ui.Mask.show(__("Wait a while"));

                if (typeof response.data.type !== "undefined" && response.data.type === "album") {
                    landingpage = Routing.generate("_album", {id: response.data.id, slug: response.data.slug}, true);
                }

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
        case "rotate":
            if (element === "image") {
                var image = parseInt(id[0]);
                var direction = id[1];
                url = Routing.generate("_photogallery_api_rotate_image", { image: image, direction: direction });
                clickIsDisabled = true;
                callback = function defaultOnSuccessCallback(r) {
                    $.each(images, function (index, element) {
                        if (element != null && element.id === image) {
                            clickIsDisabled = true;

                            var width = images[index].thumbnail.file.width;
                            var height = images[index].thumbnail.file.height;

                            images[index].thumbnail.file.width = height;
                            images[index].thumbnail.file.height = width;

                            var h = images[index].file.height;
                            var w = images[index].file.width;

                            images[index].file.height = w;
                            images[index].file.width = h;

                            var thbgurl = Routing.generate("_photogallery_api_show_thumbnail", {refresh: Math.random(), id: image, format: "jpg"});
                            images[index].thumbnail.src = thbgurl;

                            $("#img" + index).css({
                                "background-image": "url(" + thbgurl + ")",
                                "width": images[index].thumbnail.file.width
                            });

                            if (images[index].id === album.cover_id) {
                                $("#album-cover").css({
                                    "background-image": "url(" + thbgurl + ")"
                                });
                            }

                            // TODO: image preview action

                            return;
                        }
                    });
                };

            } else {
                return;
            }

            break;

        case "move-to":
        case "copy-to":
            var album_id = parseInt(id[0]);
            var image_id = parseInt(id[1]);

            url = Routing.generate("_photogallery_api_copy_image", { album: album_id, image: image_id, action: action });

            if (element === "image") {
                callback = function (data) {
                    if (action === "move-to")
                        $.each(images, function (index, element) {
                            if (element != null && element.id === image_id) {
                                if (images[index].id === album.cover_id) {
                                    album.cover.src = defaultCover;
                                    album.cover_id = 0;
                                }
                                images.splice(index, 1);
                                renderImagesView();
                                currentImage = 0;
                                bufferedImage = 1;
                                return;
                            }
                        });
                    infoBox(data.msg);
                };
            }
            else {
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
            else {
                return
            }
            break;

        case "add-images":
            if (element === "album") {
                openImagesForm(__("Images"), {
                    id: 0,
                    album_id: id,
                    title: "",
                    descripion: "",
                    is_visible: true
                });
            }
            return

        case "edit":

            switch (element) {

                case "album":
                    for (var i = 0; i < albums.length; i++) {
                        var obj = albums[i];
                        if (obj !== null && obj.id == id) {
                            openAlbumForm(__("Edit album data"), obj);
                            break;
                        }
                    }
                    break;

                case "image":
                    for (var i = 0; i < images.length; i++) {
                        var obj = images[i];

                        if (obj !== null && obj.id == id) {
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

            callback = function (data) {
                if (element === "image") {
                    $.each(images, function (index, element) {
                        if (element != null && element.id === id) {
                            images[index].is_visible = action === "show";
                            if (images[index].id === album.cover.id) {
                                album.cover.src = defaultCover
                            }
                            renderImagesView();
                            return;
                        }
                    });
                } else {
                    var landingpage = Routing.generate("_albums", true);

                    // Check if action was called in albums or images context:
                    if (location.href.indexOf(landingpage, location.href.length - landingpage.length) !== -1) {
                        $.each(albums, function (index, element) {
                            if (element != null && element.id === id) {
                                albums[index].is_visible = action === "show";
                                renderAlbumsView();
                                return;
                            }
                        });
                    } else {
                        album.is_visible = action === "show";
                        renderAlbumHeader();

                        if (album.is_visible) {
                            $("#album-cover").removeClass("hidden");
                        }
                        else {
                            $("#album-cover").addClass("hidden");
                        }
                    }
                }
            };

            break;

        case "delete":
            url = Routing.generate("_photogallery_api_delete_element", { id: id, element: element });

            callback = function (data) {
                if (element === "image") {
                    $.each(images, function (index, element) {
                        if (element != null && element.id === id) {
                            images.splice(index, 1);
                            renderImagesView();
                            renderAlbumHeader();
                            return;
                        }
                    });
                } else {
                    var landingpage = Routing.generate("_albums", true);

                    // Check if action was called in albums or images context:
                    if (location.href.indexOf(landingpage, location.href.length - landingpage.length) !== -1) {
                        $.each(albums, function (index, element) {
                            if (element != null && element.id === id) {
                                albums.splice(index, 1);
                                renderAlbumsView();
                                return;
                            }
                        });
                    } else {
                        $.ui.Mask.show();
                        location.href = landingpage;
                    }
                }
            };

            confirmDeleteBox(id, element, url, callback);
            return;

        default:
            return;
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
