// CHANGE IMAGES SEQUENCE:

function changeAlbumCover(image_id) {
    processAction("change-cover", "image", image_id, __("Changing album cover"));
}

function updateAlbum() {
    var order = [];

    $.ui.Mask.show(__("New photos sequence is being saved"));

    $(".image").each(function (index, element) {
        if ($(element).attr("id") !== "album-cover") {
            var inx = $(element).attr("id").replace(/[a-z]*/i, '');
            var id = images[inx].id;
            order.push(id);
        }
    });

    $.ajax({
        url: Routing.generate("_photogallery_api_reorder_images", { images: order.join(",") }),
        error: errorHandler,
        success: function (response) {
            var resp = {
                success: false,
                msg: response
            };

            if (typeof response.msg !== "undefined" && typeof response.success !== "undefined") {
                resp = response;
            }

            $.ui.Mask.hide();

            if (resp.success === true) {
                $("div#menu li#update-view").hide();
                $("div#menu li#undo-changes").hide();

                infoBox(__(resp.msg));
            }
            else {
                errorBox(__(resp.msg));
            }
        }
    });
}

function undoChanges() {
    var order = {};
    var count = 0;
    var cover = null;

    $("div#menu li#update-view").hide();
    $("div#menu li#undo-changes").hide();

    $(".image").each(function (index, element) {
        if ($(element).attr("id") === "album-cover") {
            cover = element;
        }
        else {
            var inx = $(element).attr("id").replace(/[a-z]*/i, '');
            order[inx] = element;
            count++;
        }
    });

    $("#images").empty();

    $("#images").append(cover);

    for (var i = 0; i < count; i++) {
        var element = order["" + i];

        $("#images").append(element);
    }
    $("#images").append('<div style="clear:both"></div>');
}

// DISPLAY LOGIC:

function displayCurrentImage(direction) {

    var image = images[currentImage];
    var format = "jpg";
    var width = image.file.width + frame * 2;
    var height = image.file.height + frame * 2;

    var arrowTop = height / 2 - $("#prev-image").height() / 2 - 10;
    var arrowLeft = -5;
    var arrowRight = image.file.width - 4 * $("#prev-image").width() + 10 - arrowLeft + 2 * frame;

    $("#prev-image").css({
        top: arrowTop,
        left: arrowLeft
    });

    $("#next-image").css({
        top: arrowTop,
        left: arrowRight
    });

    var config = {
        dialogClass: "image-preview-dialog",
        width: width,
        height: height,
        closeOnEscape: true,
        draggable: false,
        modal: true,
        buttons: {},
        resizable: false,
        open: function () {
            $(".ui-widget-overlay").bind("click", function () {
                $("#image-preview").dialog("close");
            });

            displayCurrentImage(1);

            var statusBarDescription = "&#9745; " + album.title + " (" + (1 * currentImage + 1) + "/" + (images.length) + ")";

            if (image.title !== null && image.title.length > 0) {
                statusBarDescription += " :: <span>" + image.title + "</span>";
            }

            $("#status-bar .content").html(statusBarDescription);
            $("#status-bar").show();
        },
        close: function () {
            $("#status-bar").hide();
        }
    };

    bufferImage(currentImage, images, format, direction);

    $(".image-preview-dialog").css({
        "background-image": "url(" + Routing.generate("_photogallery_api_show_image", {id: image.id, format: format}, true) + ")"
    });

    $("#image-preview").dialog(config);
}

function displayNextImage() {
    do {
        ++currentImage;
        currentImage %= images.length;
    } while (images[currentImage] === null);

    $("#image-preview").dialog("close");
    displayCurrentImage(1);
}

function displayPrevImage() {
    currentImage = currentImage == 0 ? images.length : currentImage;
    do {
        --currentImage;
    } while (images[currentImage] === null);
    $("#image-preview").dialog("close");
    displayCurrentImage(-1);
}

function bufferImage(currentImage, album, format, direction) {

    var bufferedImage = currentImage;

    if (direction > 0) {
        do {
            ++bufferedImage;
            bufferedImage %= images.length;
        } while (images[bufferedImage] === null);
    }
    else {
        bufferedImage = bufferedImage == 0 ? images.length : bufferedImage;
        do {
            --bufferedImage;
        } while (images[bufferedImage] === null);
    }

    var bufferedImageSrc = Routing.generate("_photogallery_api_show_image", {id: images[bufferedImage].id, format: format}, true);
    $("#image-buffer").attr("src", bufferedImageSrc);
}

// CONFIRMATION:

$(document).ready(function () {

    $("#next-image").click(function (event) {
        displayNextImage();
    });

    $("#prev-image").click(function (event) {
        displayPrevImage();
    });

    var albumid = parseInt(location.href.replace(/^.*\/(\d+)\/[^\/]+$/, "$1"));

    $.ajax({
        url: Routing.generate("_photogallery_api_album", { id: albumid }),
        error: errorHandler,
        success: function (response) {

            var resp = {
                success: false,
                msg: response
            };

            if (typeof response.msg !== "undefined" && typeof response.success !== "undefined") {
                resp = response;
            }

            $.ui.Mask.hide();

            if (resp.success === false) {

                if (__(response.msg) === __("Requested album is not available.")) {
                    $.ui.Mask.show();
                    location.href = Routing.generate("_albums");
                } else {
                    errorBox(__(response.msg));
                }
                return;
            }

            images = response.data;

            var temp = response.msg.split(";;;");

            var format = "jpg";

            var id = parseInt(temp[0]);
            var cover_id = parseInt(temp[4]);
            var cover = cover_id > 0
                ? Routing.generate("_photogallery_api_show_thumbnail", {id: cover_id, format: format}, true)
                : defaultCover;

            for (var i = 0; i < albums.length; i++) {
                if (albums[i].id === id) {
                    albums[i]["cover"] = {src: cover};
                    break;
                }
            }

            album = {
                id: id,
                title: temp[1],
                description: temp[2],
                is_visible: temp[3] > 0,
                cover_id: cover_id
            };

            var toolbar = getAlbumToolbarObj(album);
            var altit = album.title === "New Album" ? __(album.title) : album.title;

            $("#subtitle").html('<span '
                + (album.is_visible ? "" : ' class="hidden"') + '>'
                + altit + " (" + response.totalCount + ")</span>" + toolbar);

            $("p.info").html(album.description);

            $("li#create-new-album-menu").show();
            $("li#add-images-menu").show();

            $("#images").append('<div class="image cover" id="album-cover"></div>');

            $("#album-cover").css({
                "background-image": "url(" + cover + ")"
            });

            if (images.length > 0) {
                var format = "jpg";  // TODO: images[i].format;

                for (var i = 0; i < images.length; i++) {
                    var imgId = "img" + i;
                    var thumbnail = Routing.generate("_photogallery_api_show_thumbnail", {id: images[i].id, format: format});
                    var hidden = images[i].is_visible === false ? " hidden" : "";

                    images[i].thumbnail["src"] = thumbnail;

                    $("#images").append('<div class="image context-menu-trigger' + hidden + '" id="' + imgId + '"></div>');

                    if (images[i].is_visible === true) {
                        $("#" + imgId).css({
                            "width": images[i].thumbnail.file.width
                        });
                    }

                    if (i == images.length - 1) {
                        $("#images").append('<div style="clear:both"></div>');
                    }
                }

                setTimeout(function () {
                    for (var i = images.length - 1; i >= 0; i--) {
                        var imgId = "img" + i;

                        $("#" + imgId).css({
                            "background-image": "url(" + images[i].thumbnail.src + ")",
                            "border": "none"
                        });

                        if (images[i].is_visible === true) {
                            $("#" + imgId).css({
                                "background-color": "transparent"
                            });
                        }
                    }
                }, 1000);

                $("div#menu li#update-view").click(function (event) {
                    updateAlbum();
                });

                $("div#menu li#undo-changes").click(function (event) {
                    undoChanges();
                });

                $("#images").sortable({
                    start: function () {
                        clickIsDisabled = true;
                    },
                    stop: function () {
                        var inorder = true;

                        var inx = 0;

                        $(".image").each(function (index, element) {
                            if ($(element).attr("id") !== "album-cover") {
                                if (inx++ != $(element).attr("id").replace(/[a-z]*/i, '')) {
                                    inorder = false;
                                    return;
                                }
                            }
                        });

                        if (inorder === false) {
                            $("div#menu li#update-view").show();
                            $("div#menu li#undo-changes").show();
                        }
                        else {
                            $("div#menu li#update-view").hide();
                            $("div#menu li#undo-changes").hide();
                        }
                    }
                });

                $(".image").click(function (event) {

                    if (clickIsDisabled === true) {
                        clickIsDisabled = false;
                        return;
                    }

                    var imid = $(this).attr("id");

                    currentImage = imid === "album-cover" ? 0 : $(this).attr("id").replace(/^img/, "");

                    displayCurrentImage(1);
                });

                $("#image-preview").click(function (event) {
                    if ($(event.target).attr("id") === 'image-preview') {
                        displayNextImage();
                    }
                });

                var previewout = true;

                $("#image-preview").mouseover(function (event) {
                    previewout = false;
                    $("#image-preview div#prev-image, #image-preview div#next-image").css({
                        display: "inline-block"
                    });
                });

                $("#image-preview").mouseout(function (event) {
                    previewout = true;
                    setTimeout(function () {
                        if (previewout === true) {
                            $("#image-preview div#prev-image, #image-preview div#next-image").hide();
                        }
                    }, 500);
                });

                $.contextMenu({
                    selector: ".context-menu-trigger",

                    callback: function (action, options) {

                        switch (action) {
                            case "rotate-cw":
                            case "rotate-ccw":

                                var direction = action.replace(/^\w+\-/, "");
                                infoBox(direction);

                                break;
                            case "change-cover":
                            case "show":
                            case "hide":
                            case "delete":
                                processAction(action, "image", images[currentImage].id);
                                break;

                            default:

                                break;
                        }
                    },

                    build: function ($trigger, e) {

                        var id = $($trigger).attr("id");

                        currentImage = id == "image-preview"
                            ? currentImage
                            : id.replace(/\D+/, "");

                        var image = images[currentImage];

                        var items = {
                            "edit": {name: __("Edit"), icon: "edit"},
                            "delete": {name: __("Delete"), icon: "delete"}
                        };

                        items["rotate"] = {
                            name: __("Rotate"), icon: "rotate",
                            items: {
                                "rotate-cw": {name: __("CW"), icon: "rotate-cw" },
                                "rotate-ccw": {name: __("CCW"), icon: "rotate-ccw" }
                            }
                        };

                        if (image.is_visible === true) {
                            items["hide"] = {name: __("Hide"), icon: "hide"};
                            items["sep1"] = "---------";
                            items["change-cover"] = {disabled: image.id == album.cover_id, name: __("Use as album cover") + '&nbsp;&nbsp;', icon: "change-cover"};
                        }
                        else {
                            items["show"] = {name: __("Show"), icon: "show"};
                        }

                        items["sep2"] = "---------";
                        items["quit"] = {name: __("Quit"), icon: "quit"};

                        return {
                            items: items
                        };
                    }
                });

                bufferImage(0, album, format);
            }
            else {
                $.ui.Mask.hide();
                $("#images").append('<p style="width: 640px;float:left;margin-top:70px;text-align:center;color:gray !important;">' + __("Current album contains no photos.") + '</p>');
                $("#images").append('<div style="clear:both"></div>');
            }
        }
    });
});