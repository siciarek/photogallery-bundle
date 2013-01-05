// DISPLAY LOGIC:

function renderAlbumHeader() {
    var toolbar = getAlbumToolbar(album);
    var altit = album.title === "New Album" ? __(album.title) : album.title;
    var numberOfPhotos = images.length > 0
        ? __("number of images") + ": " + images.length
        : __("no images");
    $("#subtitle").html('<span '
        + (album.is_visible ? "" : ' class="hidden"') + '>'
        + altit + "</span>");

    $("p.number-of-photos").html(numberOfPhotos + getAlbumToolbar(album));
    $("p.number-of-photos").css({
        "margin-left": "8px"
    });

    $("p.info").html(album.description);

    if (images.length === 0) {
        album.cover_id = 0;
        album.cover.src = defaultCover;
        $("#album-cover").css({
            "background-image": "url(" + album.cover.src + ")"
        });
    }
}

function renderImagesView(delay, nocache) {

    delay = delay || 0;
    nocache = nocache || 0;

    $("#images").empty();

    $("#images").append('<div style="background-color:#a47e3c !important" class="image cover" id="album-cover"></div>');

    if (album.is_visible === false) {
        $("#album-cover").addClass("hidden");
    }

    $("#album-cover").css({
        "background-image": "url(" + album.cover.src + ")"
    });

    if (images.length > 0) {
        var format = "jpg";  // TODO: images[i].format;

        for (var i = 0; i < images.length; i++) {
            var imgId = "img" + i;
            var thumbnail = Routing.generate(route_show_thumbnail, {id: images[i].id, format: format});
            var hidden = images[i].is_visible === false ? " hidden" : "";

            images[i].thumbnail["src"] = thumbnail;

            $("#images").append('<div title="' + images[i].title + '" class="image context-menu-trigger' + hidden + '" id="' + imgId + '"></div>');

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

                var bgurl = images[i].thumbnail.src;

                bgurl += nocache == 1 ? "?" + Math.random() : "";

                $("#" + imgId).css({
                    "background-image": "url(" + bgurl + ")",
                    "border": "none"
                });

                if (images[i].is_visible === true) {
                    $("#" + imgId).css({
                        "background-color": "transparent"
                    });
                }
            }
        }, delay);
    }
}

function displayCurrentImage(direction, nocache) {

    nocache = nocache || 0;

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

            displayCurrentImage(1, nocache);

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

    var parms = {id: image.id, format: format};

    if (nocache == 1) {
        parms["refresh"] = 1;
    }

    $(".image-preview-dialog").css({
        "background-image": "url(" + Routing.generate(route_show_image, parms, true) + ")"
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

    var bufferedImageSrc = Routing.generate(route_show_image, {id: images[bufferedImage].id, format: format}, true);
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

    $.ajax({
        url: Routing.generate(route_album, { id: currentAlbumId }),
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

                if (__(response.msg) === __("Requested album is not available.") || __(response.msg) === __("Album is not available in edit mode.")) {

                    $.ui.Mask.show(__(response.msg) + ' ' + __("Wait a while"));
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
                ? Routing.generate(route_show_thumbnail, {id: cover_id, format: format}, true)
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
                cover: {src: cover},
                cover_id: cover_id
            };

            $("li#create-new-album-menu").show();
            $("li#add-images-menu").show();

            renderAlbumHeader();

            renderImagesView(1000);

            if (images.length > 0) {

                $("#images").delegate(".image", "click", function (event) {

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
                    display = images.length > 1 ? "inline-block" : "none";
                    $("#image-preview div#prev-image, #image-preview div#next-image").css("display", display);
                });

                $("#image-preview").mouseout(function (event) {
                    previewout = true;
                    setTimeout(function () {
                        if (previewout === true) {
                            $("#image-preview div#prev-image, #image-preview div#next-image").hide();
                        }
                    }, 500);
                });

                if (authenticated === true) {

                    $("div#menu li#update-view").click(function (event) {
                        reorderSequence(images, "images", ".image");
                    });

                    $("div#menu li#reset-view").click(function (event) {
                        resetView("images");
                    });

                    $("#images").sortable({
                        items: "div:not(.cover)",
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
                                $("div#menu li#reset-view").show();
                            }
                            else {
                                $("div#menu li#update-view").hide();
                                $("div#menu li#reset-view").hide();
                            }
                        }
                    });

                    $.contextMenu({
                        selector: ".context-menu-trigger",

                        callback: function (action, options) {
                            if (action.match(/^(copy|move)\-to/)) {
                                var temp = action.split(";");
                                var act = temp[0];
                                var albumid = temp[1];
                                var imageid = temp[2];
                                processAction(act, "image", [albumid, imageid]);
                            }
                            else {
                                switch (action) {
                                    case "rotate-cw":
                                    case "rotate-ccw":
                                    case "rotate-180":
                                        var direction = action.replace(/^\w+\-/, "");
                                        processAction("rotate", "image", [images[currentImage].id, direction]);
                                        break;
                                    case "change-cover":
                                    case "edit":
                                    case "show":
                                    case "hide":
                                    case "delete":
                                        processAction(action, "image", images[currentImage].id);
                                        break;

                                    default:
                                        break;
                                }
                            }
                        },

                        build: function ($trigger, e) {

                            var copyToAlbumMenuItems = {};
                            var moveToAlbumMenuItems = {};

                            var id = $($trigger).attr("id");

                            currentImage = id == "image-preview"
                                ? currentImage
                                : id.replace(/\D+/, "");

                            var image = images[currentImage];

                            var items = {
                                "edit": {name: __("Edit"), icon: "edit"},
                                "delete": {name: __("Delete"), icon: "delete"}
                            };

                            if (albums.length > 1) {
                                $.each(albums, function (index, elem) {
                                    if (elem.id != album.id) {
                                        copyToAlbumMenuItems["copy-to;" + elem.id + ";" + image.id] = { name: elem.title + '&nbsp;&nbsp;' };
                                        moveToAlbumMenuItems["move-to;" + elem.id + ";" + image.id] = { name: elem.title + '&nbsp;&nbsp;'};
                                    }
                                });

                                items["copy-to"] = {
                                    name: __("Copy to"),
                                    icon: "copy",
                                    items: copyToAlbumMenuItems
                                }

                                items["move-to"] = {
                                    name: __("Move to"),
                                    icon: "move",
                                    items: moveToAlbumMenuItems
                                }
                            }

                            if (id != "image-preview") {
                                items["rotate"] = {
                                    name: __("Rotate"),
                                    icon: "rotate",
                                    items: {
                                        "rotate-cw": {name: __("CW"), icon: "rotate-cw" },
                                        "rotate-ccw": {name: __("CCW"), icon: "rotate-ccw" },
                                        "rotate-180": {name: __("180&deg;"), icon: "upside-down" }
                                    }
                                };
                            }

                            if (image.is_visible === true) {
                                items["hide"] = {name: __("Hide"), icon: "hide"};
                                items["sep1"] = "---------";
                                items["change-cover"] = {
                                    disabled: image.id == album.cover_id,
                                    name: __("Use as album cover") + '&nbsp;&nbsp;',
                                    icon: "change-cover"
                                };
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
                }

                bufferImage(0, album, format);
            }
            else {
                $.ui.Mask.hide();
                $("#images").append('<p style="width: 640px;float:left;margin-top:70px;text-align:center;color:gray !important;">' + __("Album contains no photos.") + '</p>');
                $("#images").append('<div style="clear:both"></div>');
            }
        }
    })
    ;
})
;