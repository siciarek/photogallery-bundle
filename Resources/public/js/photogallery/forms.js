//function addTinyMCE() {
//    jQuery('#description-album').tinymce({
//        width: "550px",
//        height: "290px",
//        mode: "textarea",
//        // General options
//        theme : "simple",
//        script_url: "/bundles/stfalcontinymce/vendor/tiny_mce/tiny_mce.js"
//    });
//}
//
//function removeTinyMCE () {
//    tinyMCE.execCommand('mceFocus', false, 'email_body');
//    tinyMCE.execCommand('mceRemoveControl', false, 'email_body');
//}

var imagesFormValidator = null;

function openElementForm(title, element, data) {

    data = data || {
        id: 0,
        album_id: 0,
        title: "",
        descripion: "",
        is_visible: true
    };

    var reset = __("Reset");
    var save = __("Save");
    var cancel = __("Cancel");

    var buttons = {};

    buttons[reset] = function (event) {
        imagesFormValidator.resetForm();
        $("#files-to-upload-" + element + "").empty();
        var nofid = "#number-of-chosen-files-" + element + "";
        $(nofid).html("");
    };

    buttons[save] = function (event) {
        $("#" + element + "-form form").submit();
    };

    buttons[cancel] = function (event) {
        $("#" + element + "-form").dialog("close");
    };

    $("#" + element + "-form").dialog({
        title: getTitle(title, "image"),
        dialogClass: "photogallery-form",
        width: 850,
        height: 530,
        closeOnEscape: true,
        draggable: true,
        modal: true,
        resizable: false,
        buttons: buttons,

        open: function () {
            $(".ui-widget-overlay").bind("click", function () {
                $("#" + element + "-form").dialog("close");
            });

            $('.ui-dialog-buttonpane').find('button:contains("' + __("Reset") + '")').button({
                icons: {
                    primary: 'ui-icon-circle-arrow-w'
                }
            });

            $('.ui-dialog-buttonpane').find('button:contains("' + __("Save") + '")').button({
                icons: {
                    primary: 'ui-icon-circle-check'
                }
            });

            $('.ui-dialog-buttonpane').find('button:contains("' + __("Cancel") + '")').button({
                icons: {
                    primary: 'ui-icon-circle-close'
                }
            });

            $("#label-photos-images").show();

            $(".cabinet").css({
                "display": "none"
            });

            $(".form-colum.right").css({
                "background-image": "none",
                "background-repeat": "no-repeat",
                "background-position": "center",
                "border": "none"
            });

            $(".form-colum.right div.form-field, .form-colum.right div.files-list").show();
            $("input[id^=photos]").removeAttr("disabled");

            if (element === "images" && data.id > 0) {
                console.log("Do not validate file upload");
                var thumbnail = defaultCover;
                $("input[id^=photos]").attr("disabled", "disabled");
                $.each(images, function (index, element) {
                    if (element.id == data.id) {
                        thumbnail = element.thumbnail.src;
                    }
                });

                $(".form-colum.right").css({
                    "background-image": "url(" + thumbnail + ")",
                    "border": "1px solid silver"
                });
                $(".form-colum.right div.form-field, .form-colum.right div.files-list").hide();
            }

            $("select[name='album']").val(data.album_id);

            if(data.album_id === 0) {
                $("#" + element + "-form form").get(0).reset();
                $("label.error").hide();
                $(".error").removeClass("error");
            }

            $("input[name='id']").val(data.id);
            $("input[name='title']").val(data.title);
            $("textarea[name='description']").val(data.description);
            $("input[name='publish']").attr("checked", data.is_visible);
        },

        close: function () {
            imagesFormValidator.resetForm();
            $("#files-to-upload-" + element + "").empty();
            var nofid = "#number-of-chosen-files-" + element + "";
            $(nofid).html("");
        }
    }).show();
}

function openAlbumForm(title, data) {
    title = title || __("Album");

    openElementForm(title, "album", data);
}

function openImagesForm(title, data) {
    title = title || __("Images");

    openElementForm(title, "images", data);
}

function showFileDialog(label) {
    var id = $(label).attr("id").replace(/^\w+\-/, "#");
    $(id).trigger("click");
}

function setImageInfo(index, key) {
    currentImageInfoElement = key;

    if (typeof imagesInfo[currentImageInfoElement] === "undefined") {
        return true;
    }

    var imgdata = imagesInfo[currentImageInfoElement];

    $("#images-form form input[name='title']").val(imgdata.title);
    $("#images-form form textarea[name='description']").val(imgdata.description);
    $("#images-form form input[name='publish']").attr("checked", imgdata.is_visible);

    $("#files-to-upload-images").children().each(function (i, elem) {
        $(elem).removeClass("selected");
        if (i === index) {
            $(elem).addClass("selected");
        }
    });
}

function updateImageInfo() {

    if (typeof imagesInfo[currentImageInfoElement] === "undefined") {
        return true;
    }

    imagesInfo[currentImageInfoElement].is_visible =
        $("#images-form form input:checkbox[name='publish']:checked").val() === "on";
    imagesInfo[currentImageInfoElement].title = $("#images-form form input[name='title']").val();
    imagesInfo[currentImageInfoElement].description = $("#images-form form textarea[name='description']").val();

    var json = JSON.stringify(imagesInfo);

    $("#images-form form input[name='imginfo']").val(json);
}

$(document).ready(function () {

    $.ajax({
        url: Routing.generate("_photogallery_api_album_list"),
        async: false,
        error: errorHandler,

        success: function (data, textStatus, jqXHR) {

            var onsuccess = function (response) {
                albums = response.data;

                if (albums.length > 1) {
                    $("select#album-images").append('<option value="0">' + __("Choose album") + '</option>');
                }

                for (var i = 0; i < albums.length; i++) {
                    var selected = currentAlbumId > 0 && currentAlbumId === albums[i].id || albums.length === 1
                        ? ' selected="selected"'
                        : "";
                    $("select#album-images").append("<option value='" + albums[i].id + "'" + selected + ">" + albums[i].title + "</option>");
                }
            };

            successHandler(data, textStatus, jqXHR, onsuccess);
        }
    });

    $("[class=cabinet]").mouseover(function (event) {
        $(this).css({
            "background-image": "url(/bundles/siciarekphotogallery/images/btn-choose-file-on.png)"
        });
    });

    $("[class=cabinet]").mouseout(function (event) {
        $(this).css({
            "background-image": "url(/bundles/siciarekphotogallery/images/btn-choose-file-off.png)"
        });
    });

    imagesFormValidator = $("#images-base-form").validate({
        rules: {
            album: {
                validalbum: true
            },
            "photos[]": {
                validimages: true
            }
        },
        messages: {

        },
        submitHandler: formAction
    });

    $("#album-base-form").validate({
        rules: {
            title: {
                validtitleordescription: true
            },
            description: {
                validtitleordescription: true
            },
            "photos[]": {
                validimages: true
            }
        },
        messages: {

        },
        submitHandler: formAction
    });

    $("#images-form form input:checkbox[name='publish']").click(updateImageInfo);
    $("#images-form form input[name='title'], #images-form form textarea[name='description']").change(updateImageInfo);

    $("form  input[id^=photos]").change(function (event) {

        if (currentAlbumId == 0) {
            errorBox(__("Album is required."));
            return false;
        }

        var filesField = $(this);
        var files = filesField.prop("files");
        var element = filesField.attr("id").replace(/^\w+\-/, "");

        $("#files-to-upload-" + element).empty();
        imagesInfo = {};

        var totalsize = 0;

        valid = true;

        var invalidFileName = null;

        $.each(files, function (index, elem) {
            if (elem.type.match(/^image\//) === null) {
                invalidFileName = elem.name;
                filesField.val("");
                valid = false;
                return;
            }
        });

        if (valid === false) {
            errorBox("File \"" + invalidFileName + "\" has unsupported format.");
        } else {

            $(files).each(function (index, elem) {
                var imgkey = "" + index + elem.name;
                var filesize = parseSize(elem.size);
                totalsize += elem.size;
                var imgtitle = parseImageTitle(elem.name);

                $("#files-to-upload-" + element).append("<li onclick=\"setImageInfo(" + index + ", '" + imgkey + "')\">" + elem.name + " (" + filesize + ")" + "</li>");
                imagesInfo[imgkey] = {
                    id: 0,
                    title: imgtitle,
                    description: null,
                    is_visible: true,
                    album_id: parseInt(currentAlbumId)
                }
            });

            var nof = "(" + files.length + "/" + parseSize(totalsize) + ")";
            $("#number-of-chosen-files-" + element).html(nof);
        }
    });
});