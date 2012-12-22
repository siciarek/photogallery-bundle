
function displayCurrentImage(direction) {

    var image = album[currentImage];
    var format = "jpg";
    var width = image.width + frame * 2;
    var height = image.height + frame * 2;

    var arrowTop = height / 2 - $("#prev-image").height() / 2 - 10;
    var arrowLeft = -5;
    var arrowRight = image.width - 4 * $("#prev-image").width() + 10 - arrowLeft + 2 * frame;

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

            var statusBarDescription = "&#9745; " + albumName + " (" + (1 * currentImage + 1) + "/" + (album.length) + ")";

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

    bufferImage(currentImage, album, format, direction);

    $(".image-preview-dialog").css({
        "background-image": "url(" + Routing.generate("_photogallery_api_show_image", {id: image.id, slug: "image", format: format}, true) + ")"
    });

    $("#image-preview").dialog(config);
}

function bufferImage(currentImage, album, format, direction) {

    var bufferedImage = currentImage;

    if (direction > 0) {
        ++bufferedImage;
        bufferedImage %= album.length;
    }
    else {
        bufferedImage = bufferedImage == 0 ? album.length : bufferedImage;
        --bufferedImage;
    }

    var bufferedImageSrc = Routing.generate("_photogallery_api_show_image", {id: album[bufferedImage].id, slug: "image", format: format}, true);
    $("#image-buffer").attr("src", bufferedImageSrc);
}

function deletePhotos(album, ids, buttons) {

    var photos = ids.join(",");

    $.ajax({
        url: Routing.generate("_photogallery_api_delete_photos", { album: album, photos: photos }),
        type: "POST",

        error: function (response) {
            $("#confirmation-dialog").dialog("close");
            enableButtons(buttons);
            errorBox("Unexpected Exception.");
        },

        success: function (response) {

            var defresp = {
                success: false,
                msg: response
            };

            var resp = typeof response.msg === "undefined" ? defresp : response;

            if (response.success === true) {
                location.reload();
            }
            else {
                enableButtons(buttons);
                $("#confirmation-dialog").dialog("close");
                errorBox(response.msg);
            }
        }
    });
}

function confirmationBox(image, action) {
    var msg = __("Are you sure you want to delete this photo?");

    var icon = action === "delete" ? "trash" : "image";

    var yes = __("Yes");
    var no = __("No");

    var confirmationDialog = {
        title: getTitle(__(title), icon),
        dialogClass: "photogallery-form",
        width: 350,
        height: 330,
        closeOnEscape: false,
        draggable: true,
        resizable: false,
        modal: true,
        buttons: {
            yes: function (event) {
                var ids = [image.id];
                $("#confirmation-dialog #confirmation-message")
                    .html(__("Deleting photo in progress") + "&hellip;");
                var buttons = $('.ui-dialog-buttonpane').find('button');
                disableButtons(buttons);

                if (action === "delete") {
                    deletePhotos(albumId, ids, buttons);
                }
            },
            no: function (event) {
                $("#confirmation-dialog").dialog("close");
            }
        },
        open: function () {
            $("#confirmation-dialog #confirmation-message")
                .html(msg);
            $("#confirmation-dialog").css({
                "background": "url(" + image.thumbnail.src + ") center no-repeat"
            });

            $(".ui-dialog-titlebar-close").css({
                "display": "none"
            });
        },
        close: function () {

        }
    };

    $("#confirmation-dialog").dialog(confirmationDialog);
}

function displayNextImage() {
    ++currentImage;
    currentImage %= album.length;
    $("#image-preview").dialog("close");
    displayCurrentImage(1);
}

function displayPrevImage() {
    currentImage = currentImage == 0 ? album.length : currentImage;
    --currentImage;
    $("#image-preview").dialog("close");
    displayCurrentImage(-1);
}

function updateAlbum() {
    var order = [];

    $(".image").each(function(index, element) {
        var inx = $(element).attr("id").replace(/[a-z]*/i, '');
        var id = album[inx].id;
        order.push(id);
    });

    $.ajax({
        url: Routing.generate("_photogallery_api_reorder_photos", { album: albumId, photos: order.join(",") }),
        error: function (response) {
            errorBox("Unexpected Exception.");
        },
        success: function (response) {
            var resp = {
                success: false,
                msg: response
            };

            if(typeof response.msg !== "undefined" && typeof response.success !== "undefined") {
                resp = response;
            }

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

    $("div#menu li#update-view").hide();
    $("div#menu li#undo-changes").hide();

    $(".image").each(function(index, element) {
        var inx = $(element).attr("id").replace(/[a-z]*/i, '');
        order[inx] = element;
        count++;
    })

    $("#images").empty();

    for(var i = 0; i < count; i++) {
        var element = order["" + i];

        $("#images").append(element);
    }
    $("#images").append('<div style="clear:both"></div>');
}


$(document).ready(function () {

    $("#next-image").click(function (event) {
        displayNextImage();
    });

    $("#prev-image").click(function (event) {
        displayPrevImage();
    });

    $.ajax({
        url: Routing.generate("_photogallery_api_album", { id: albumId }),
        error: function (response) {
            errorBox("Unexpected Exception.");
        },
        success: function (response) {
            album = response.data;

            if (typeof response.msg === "undefined") {
                errorBox(response);
                return;
            }

            if (response.success === false) {
                errorBox(__(response.msg));
                return;
            }

            var temp = response.msg.split(";;;");
            albumName = temp[0];
            albumDescription = temp[1];

            $("#subtitle").html(albumName + " (" + response.totalCount + ")");
            $("p.info").html(albumDescription);

            $("li#create-new-album-menu").show();
            $("li#add-photos-menu").show();

            if (album.length > 0) {
                var format = "jpg";  // TODO: album[i].format;

                for (var i = 0; i < album.length; i++) {
                    var imgId = "img" + i;
                    var thumbnail = Routing.generate("_photogallery_api_show_thumbnail", {id: album[i].id, format: format});

                    album[i].thumbnail["src"] = thumbnail;

                    $("#images").append('<div class="image" id="' + imgId + '"></div>');

                    $("#" + imgId).css({
                        "width": album[i].thumbnail.width
                    });

                    if (i == album.length - 1) {
                        $("#images").append('<div style="clear:both"></div>');
                    }
                }

                setTimeout(function () {
                    for (var i = 0; i < album.length; i++) {
                        var imgId = "img" + i;
                        var thumbnail = Routing.generate("_photogallery_api_show_thumbnail", {id: album[i].id, format: format});
                        $("#" + imgId).css({
                            "border": "none",
                            "background-color": "transparent",
                            "background-image": "url(" + thumbnail + ")"
                        });

                    }
                }, 1000);

                $("div#menu li#update-view").click(function(event){
                    updateAlbum();
                });

                $("div#menu li#undo-changes").click(function(event){
                    undoChanges();
                });

                $("#images").sortable({
                    start: function() {
                        clickIsDisabled = true;
                    },
                    stop: function() {
                        var inorder = true;

                        $(".image").each(function(index, element) {
                            if(index != $(element).attr("id").replace(/[a-z]*/i, '')) {
                                inorder = false;
                                return;
                            }
                        });

                        if(inorder === false) {
                            $("div#menu li#update-view").show();
                            $("div#menu li#undo-changes").show();
                        }
                        else  {
                            $("div#menu li#update-view").hide();
                            $("div#menu li#undo-changes").hide();
                        }
                    }
                });

                $(".image").click(function (event) {

                    if(clickIsDisabled === true) {
                        clickIsDisabled = false;
                        return;
                    }

                    currentImage = $(this).attr("id").replace(/^img/, "");
                    displayCurrentImage(1);
                });

                $("#image-preview").click(function (event) {
                    if($(event.target).attr("id") === 'image-preview') {
                        displayNextImage();
                    }
                });

                $.contextMenu({
                    selector: ".image",
                    callback: function (key, options) {

                        var id = $(this).attr("id").replace(/\D+/, "");
                        var image = album[id];

                        switch (key) {
                            case "change-cover":
                                confirmationBox(image, key);
                                break;
                            case "delete":
                                confirmationBox(image, key);
                                break;

                            default:
                                break;
                        }
                    },
                    items: contextMenuItems
                });

                bufferImage(0, album, format);
            }
            else {
                $("#images").append('<p style="margin-top:100px;text-align:center;color:gray !important;">' + __("Current album contains no photos.") + '</p>');
            }
        }
    });
});