

function loadAlbumPhotos(albums) {

    $("li#create-new-album-menu").show();

    if (albums.length > 0) {

        $("li#add-images-menu").show();

        for (var i = 0; i < albums.length; i++) {

            var format = "jpg";
            var albumId = "album" + albums[i].id;
            var descId = "desc" + i;
            var title = albums[i].title;
            var hidden = albums[i].is_visible === false ? " hidden" : "";
            var numberOfPhotos = albums[i].images.length > 0
                ?  __("number of images") + ": " + albums[i].images.length
                : __("no images");
            var cover = albums[i].cover !== null
                ? Routing.generate("_photogallery_api_show_thumbnail", {id: albums[i].cover.id, format: format}, true)
                : defaultCover;

            if(cover === defaultCover) {
                albums[i].cover = {"src" : cover};
            }

            albums[i].cover["src"] = cover;

            var description = albums[i].description !== null ? albums[i].description : "";

            var toolbar = getAlbumToolbar(i);

            $("#albums").append("<div class='description' id='" + descId + "'></div>");

            $("#albums div.description#" + descId).append('<div class="image cover' + hidden + '" id="' + albumId + '"></div>');

            $("#" + albumId + "").css({
                "background-image": "url(" + cover + ")"
            });

            if (title !== null && title !== "") {
                $("#albums div.description#" + descId).append('<h2 class="' + hidden + '">' + title + '</h2>');
            }

            $("#albums div.description#" + descId).append("<p class='number-of-photos'>"
                + numberOfPhotos
                + toolbar
                + "</p>");
            $("#albums div.description#" + descId).append('<p class="' + hidden + '">' + description + '</p>');
            $("#albums").append('<div class="separator"></div>');
        }
    }
    else {
        $("#albums").append('<p style="margin-top:100px;text-align:center;color:gray !important;">' + __("Gallery contains no albums.") + '</p>');
    }

    $("div#menu li#update-view").click(function (event) {
        reorderSequence(albums, "albums", ".description");
    });

    $("div#menu li#undo-changes").click(function (event) {
        undoChanges(".description", "#albums");
    });

    $("#albums").sortable({
        start: function() {
            clickIsDisabled = true;
        },
        stop: function() {
            var inorder = true;

            $("#albums div.description").each(function(index, element) {
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

    $( "#albums" ).disableSelection();

    $(".description").click(function (event) {

        if($(event.target).hasClass("action")) {
            return;
        }

        if(clickIsDisabled === true) {
            clickIsDisabled = false;
            return;
        }

        var id = $(this).find("[id^='album']").attr("id").replace(/[a-z]*/i, '');

        var slug = "album";

        for(var i = 0; i < albums.length; i++) {
            if(albums[i].id == id) {
                if(albums[i].slug.length > 0) {
                    slug = albums[i].slug;
                }
                break;
            }
        }

        location.href = Routing.generate("_album", {id: id, slug: slug}, true);
    });
}

$(document).ready(function () {


    if (albums === null) {

        $.ajax({
            url: Routing.generate("_photogallery_api_album_list"),
            error: errorHandler,
            success: function (response) {
                $.ui.Mask.hide();
                albums = response;
            }
        });
    }
    else
    {
        $.ui.Mask.hide();
    }

    loadAlbumPhotos(albums)
});
