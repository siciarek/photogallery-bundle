(function ($, W, D) {
    var JQUERY4U = {};

    $.validator.addMethod(
        "validalbum",
        function (value, element) {
            return value > 0;
        },
        $.format(__("Album is required."))
    );

    $.validator.addMethod(
        "validimages",
        function (value, element) {
            var suffix = $(element).attr("id").split("-").pop();
            var valid = value.length > 0;
            var files = $(element).prop("files");

            valid = suffix === "album" || valid

            if (valid === false) {
                errorBox(__("cAt least one image is required."));
                return false;
            }

            if (valid === true) {
                $.each(files, function (index, elem) {
                    if (elem.type.match(/^image\//) === null) {
                        errorBox("File \"" + elem.name + "\" has unsupported format.");
                        valid = false;
                        return;
                    }
                });
            }

            return valid;
        },
        $.format(__("At least one image is required."))
    );

    $.validator.addMethod(
        "validtitleordescription",
        function (value, element) {
            var suffix = $(element).attr("id").split("-").pop();
            var titleid = "title-" + suffix;
            var descriptionid = "description-" + suffix;

            var title = $("#" + titleid).val();
            var description = $("#" + descriptionid).val();
            var value = "" + title + "" + description;

            if (value.length > 0) {
                $("label[for='" + titleid + "'].error").hide();
                $("label[for='" + descriptionid + "'].error").hide();
            }

            return value.length > 0;
        },
        $.format(__("Title or description is required."))
    );

})(jQuery, window, document);