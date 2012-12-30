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

function openElementForm(title, element, data) {

    data = data || {
        id: 0,
        title: "",
        descripion: "",
        is_visible: true
    };

    var reset = __("Reset");
    var save = __("Save");
    var cancel = __("Cancel");

    var buttons = {};

    buttons[reset] = function (event) {
        $("#" + element + "-form form").get(0).reset();
        $("#files-to-upload-" + element + "").empty();
        var nofid = "#number-of-chosen-files-" + element + "";
        var nof = $(nofid).text().replace(/\d+/, 0);
        $(nofid).html(nof);
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
        height: 500,
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


            $("input[name='id']").val(data.id);
            $("input[name='title']").val(data.title);
            $("textarea[name='description']").val(data.description);
            $("input[name='hidden']").attr("checked", !data.is_visible);

        },

        close: function () {
            $("#" + element + "-form form").get(0).reset();
            $("#files-to-upload-" + element + "").empty();
            var nofid = "#number-of-chosen-files-" + element + "";
            var nof = $(nofid).text().replace(/\d+/, 0);
            $(nofid).html(nof);
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

$(document).ready(function () {

    $.ajax({
        url: Routing.generate("_photogallery_api_album_list"),
        async: false,
        error: function (response) {
            errorBox("Unexpected Exception.");
        },
        success: function (response) {
            albums = response;

            if(albums.length > 1) {
                $("select#album-images").append('<option value="0">' + __("Choose album") + '</option>');
            }

            for (var i = 0; i < albums.length; i++) {
                var selected = currentAlbumId > 0 && currentAlbumId === albums[i].id || albums.length === 1
                    ? ' selected="selected"'
                    : "";
                $("select#album-images").append("<option value='" + albums[i].id + "'" + selected + ">" + albums[i].title + "</option>");
            }
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

    $("#album-form form").validate({
        submitHandler: formAction
    });

    $("#images-form form").validate({
        submitHandler: formAction
    });

    $("form  input[id^=photos]").change(function (event) {

        var files = $(this).prop("files");
        var sufix = $(this).attr("id").replace(/^\w+\-/, "");

        var nof = $("#number-of-chosen-files-" + sufix).text().replace(/\d+/, files.length);
        $("#number-of-chosen-files-" + sufix).html(nof);

        $("#files-to-upload-" + sufix).empty();

        $(files).each(function (index, elem) {
            var filesize = parseSize(elem.size);
            $("#files-to-upload-" + sufix).append("<li>" + elem.name + " (" + filesize + ")" + "</li>");
        });
    });

});