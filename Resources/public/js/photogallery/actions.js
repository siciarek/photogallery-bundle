
function processAction(action, element, id, message) {

    message = message || "Wait a while";
    var url = null;
    var params = {};


    switch(action) {
        case "edit":
            console.log(["processAction", action, element, id]);
            return;

        case "show":
        case "hide":
            url = Routing.generate("_photogallery_api_show_hide_element", { id: id, action: action, element: element });
            break;

        case "delete":
            url = Routing.generate("_photogallery_api_delete_element", { id: id, element: element });
            break;

        default:
            break;
    }

    $.ui.Mask.show(__(message));
    remoteAction(url, params);
}

function remoteAction(url, params) {

    params = params || {};

    $.ajax({
        url: url,
        data: params,
        error: function (response) {
            $.ui.Mask.hide();
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

            $.ui.Mask.hide();

            if (resp.success === true) {
                // TODO: no reload action
                $.ui.Mask.show(__("Wait a while"));
                location.reload();
            }
            else {
                errorBox(__(resp.msg));
            }
        }
    });
}

function confirmationBox(index, action, element) {

    var msg = __("Are you sure you want to delete this " + element + "?");

    var icon = action === "delete" ? "trash" : "image";
    var yes = __("Yes");
    var no = __("No");

    var image = album[index];

    var buttons = {};

    buttons[yes] = function (event) {

        $("#confirmation-dialog").dialog("close");

        switch(action) {
            case "delete":
                $("#image-preview").hide();
                processAction(action, "image", image.id);
                break;
            default:
                break;
        }
    };

    buttons[no] = function (event) {
        $("#confirmation-dialog").dialog("close");
    };

    $("#confirmation-dialog").dialog({
        title: getTitle(__(title), icon),
        dialogClass: "photogallery-form",
        width: 350,
        height: 330,
        closeOnEscape: false,
        draggable: true,
        resizable: false,
        modal: true,
        buttons: buttons,
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
    });
}

function getActionButton(action, object, id) {
    var button = "";
    button += '<span title="' + __(action) + '" class="' + action + ' ' + object + ' action" id="element' + id + '">&nbsp;</span>';

    return button;
}
