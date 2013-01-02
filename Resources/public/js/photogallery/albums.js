function renderAlbumsView() {

    $("#albums").empty();

    for (var i = 0; i < albums.length; i++) {

        var album = albums[i];

        var albumId = "album" + album.id;
        var descId = "desc" + i;
        var descdiv = "#albums div.description#" + descId;

        var numberOfPhotos = album.images.length > 0
            ? __("number of images") + ": " + album.images.length
            : __("no images");
        var title = album.title;
        var description = album.description !== null ? album.description : "";


        $("#albums").append("<div class='description' id='" + descId + "'></div>");

        $(descdiv).append('<div id="' + albumId + '"></div>');
        $("#" + albumId).css("background-image", "url(" + album.cover.src + ")");
        $("#" + albumId).addClass("image");
        $("#" + albumId).addClass("cover");

        var hidden = "";

        if(album.is_visible == false) {
            $("#" + albumId).addClass("hidden");
            hidden = "hidden";
        }

        if (title !== null && title !== "") {
            $(descdiv).append('<h2 class="' + hidden + '">' + title + '</h2>');
        }

        $(descdiv).append('<p class="number-of-photos">' + numberOfPhotos + getAlbumToolbarObj(album) + "</p>");

        if (description !== "") {
            $(descdiv).append('<p class="' + hidden + '">' + description + '</p>');
        }

        $("#albums").append('<div class="separator"></div>');
    }
}

function loadAlbumPhotos(albums) {

    $("li#create-new-album-menu").show();

    if (albums.length > 0) {

        $("li#add-images-menu").show();

        for (var i = 0; i < albums.length; i++) {

            var format = "jpg";
            var cover = albums[i].cover !== null
                ? Routing.generate("_photogallery_api_show_thumbnail", {id: albums[i].cover.id, format: format}, true)
                : defaultCover;

            if (cover === defaultCover) {
                albums[i].cover = {"src": cover};
            }

            albums[i].cover["src"] = cover;
        }

        renderAlbumsView();
    }
    else {
        $("#albums").append('<p style="margin-top:100px;text-align:center;color:gray !important;">' + __("Gallery contains no albums.") + '</p>');
    }

    if (authenticated === true) {

        $("div#menu li#update-view").click(function (event) {
            reorderSequence(albums, "albums", ".description");
        });

        $("div#menu li#reset-view").click(function (event) {
            resetView("albums");
        });

        $("#albums").sortable({
            start: function () {
                clickIsDisabled = true;
            },
            stop: function () {
                var inorder = true;

                $("#albums div.description").each(function (index, element) {
                    if (index != $(element).attr("id").replace(/[a-z]*/i, '')) {
                        inorder = false;
                        return;
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
    }

    $("#albums")
        .disableSelection()
        .delegate(".description", "click", function (event) {

            if (clickIsDisabled === true) {
                clickIsDisabled = false;
                return;
            }

            if ($(event.target).hasClass("action")) {
                return;
            }

            var id = $(this).find("[id^='album']").attr("id").replace(/[a-z]*/i, '');
            var slug = "album";

            for (var i = 0; i < albums.length; i++) {
                if (albums[i].id == id) {
                    if (albums[i].slug.length > 0) {
                        slug = albums[i].slug;
                    }
                    break;
                }
            }

            location.href = Routing.generate("_album", {id: id, slug: slug}, true);
        });
}

$(document).ready(function () {
    $.ui.Mask.hide();
    loadAlbumPhotos(albums)
});
