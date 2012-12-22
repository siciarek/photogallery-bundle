function loadAlbumPhotos(albums) {
    $("#subtitle").html(pageTitle);

    $("li#create-new-album-menu").show();

    if (albums.length > 0) {

        $("li#add-photos-menu").show();

        for (var i = 0; i < albums.length; i++) {

            var format = "jpg";
            var albumId = "album" + albums[i].id;
            var descId = "desc" + i;
            var title = albums[i].title;
            var numberOfPhotos = __("number of photos") + ": " + albums[i].images.length;
            var cover = albums[i].cover !== null
                ? Routing.generate("_photogallery_api_show_thumbnail", {id: albums[i].cover.id, slug: "cover", format: format}, true)
                : defaultCover;

            if (albums[i].images.length === 0) {
                numberOfPhotos = __("no photos");
            }

            var description = albums[i].description;

            $("#albums").append("<div class='description' id='" + descId + "'></div>");

            $("#albums div.description#" + descId).append("<div class='image' id='" + albumId + "'></div>")

            $("#" + albumId + "").css({
                "background-image": "url(" + cover + ")"
            });
            if (title !== null && title !== "") {
                $("#albums div.description#" + descId).append("<h2>" + title + "</h2>");
            }

            $("#albums div.description#" + descId).append("<p style='color:#AAAAAA !important;font-style:normal;'>" + numberOfPhotos + "</p>");
            $("#albums div.description#" + descId).append("<p>" + description + "</p>");
            $("#albums").append('<div class="separator"></div>');
        }
    }
    else {
        $("#albums").append('<p style="margin-top:100px;text-align:center;color:gray !important;">' + __("Gallery contains no albums.") + '</p>');
    }

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

//    $( "#albums" ).disableSelection();

    $(".image").click(function (event) {

        if(clickIsDisabled === true) {
            clickIsDisabled = false;
            return;
        }

        var id = $(this).attr("id").replace(/[a-z]*/i, '');
        location.href = Routing.generate("_album", {id: id, slug: "album"}, true);
    });
}

$(document).ready(function () {

    if (albums.length == 0) {
        $.ajax({
            url: Routing.generate("_photogallery_api_album_list"),
            error: function (response) {
                errorBox("Unexpected Exception.");
            },
            success: function (response) {

                albums = response;
            }
        });
    }

    loadAlbumPhotos(albums)
});