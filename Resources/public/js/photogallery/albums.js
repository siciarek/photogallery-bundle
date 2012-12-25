

function getAlbumToolbar(index) {
    var album = albums[index];
    var showHide = albums[index].is_visible === true ? "hide" : "show";

    var toolbar = "";
    toolbar += '<span class="toolbar">';

    toolbar += getActionButton("edit",   "album", album.id);
    toolbar += getActionButton(showHide, "album", album.id);
    toolbar += getActionButton("delete", "album", album.id);

    toolbar += '</span>';
    return toolbar;
}

function loadAlbumPhotos(albums) {

    $("li#create-new-album-menu").show();

    if (albums.length > 0) {

        $("li#add-photos-menu").show();

        for (var i = 0; i < albums.length; i++) {

            var format = "jpg";
            var albumId = "album" + albums[i].id;
            var descId = "desc" + i;
            var title = albums[i].title;
            var hidden = albums[i].is_visible === false ? " hidden" : "";
            var numberOfPhotos = __("number of photos") + ": " + albums[i].images.length;
            var cover = albums[i].cover !== null
                ? Routing.generate("_photogallery_api_show_thumbnail", {id: albums[i].cover.id, slug: "cover", format: format}, true)
                : defaultCover;

            if(cover === defaultCover)
                albums[i].cover = {"src" : cover};
            else
                albums[i].cover["src"] = cover;

            if (albums[i].images.length === 0) {
                numberOfPhotos = __("no photos");
            }

            var description = albums[i].description !== null ? albums[i].description : "";

            var toolbar = getAlbumToolbar(i);

            $("#albums").append("<div class='description' id='" + descId + "'></div>");

            $("#albums div.description#" + descId).append('<div class="image' + hidden + '" id="' + albumId + '"></div>');

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

    $("div#albums").delegate("span.action", "click", function(event){
        var cls = $(event.target).attr("class").split(" ");
        var action = cls.shift();
        var object = cls.shift();
        var id = parseInt($(event.target).attr("id").replace(/\D+/, ''));

        processAction(action, object, id);
    });

    if (albums === null) {

        $.ajax({
            url: Routing.generate("_photogallery_api_album_list"),
            error: function (response) {
                errorBox("Unexpected Exception.");
            },
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
