require("./bootstrap");

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    loadFiles();
});

function loadFiles() {
    $.ajax({
        type: "GET",
        url: "/refresh",
        success: function () {},
    });
}

window.Echo.channel(`action-channel-one`).listen("ActionEvent", (response) => {
    $("#data_table tbody").empty();
    response.batchData.forEach((data) => {
        var currentTime = new Date(data.CreatedAt).toLocaleString();
        appendToTable(currentTime, data.FileName, data.Status);
    });
});

function appendToTable(createdAt, FileName, Status) {
    var markup =
        "<tr><td>" +
        createdAt +
        "</td><td>" +
        FileName +
        "</td><td>" +
        Status +
        "</td></tr>";
    $("#data_table tbody").prepend(markup);
}

$("#file_upload_form").submit(function (e) {
    e.preventDefault();
    $("#progress_wrapper").removeClass("d-none"); //Show progress bar
    $("#submit_btn").addClass("d-none"); //Hide upload btn
    $("#loading_btn").removeClass("d-none"); //Show Uploading
    $.ajax({
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener(
                "progress",
                function (evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = (
                            (evt.loaded / evt.total) *
                            100
                        ).toFixed(0);
                        $("#progress")
                            .css("width", percentComplete + "%")
                            .attr("aria-valuenow", percentComplete); //Show progress
                        $("#progress_status").html(
                            `${percentComplete} % Uploaded`
                        );
                        if (percentComplete == 100) {
                            $("#progress_wrapper").addClass("d-none"); //Show progress bar
                            var currentTime = new Date().toLocaleString();
                            var fileName = $("input[type=file]")
                                .val()
                                .split("\\")
                                .pop()
                                .split(".")
                                .shift();
                            appendToTable(currentTime, fileName, "Pending");
                            $("#loading_btn").addClass("d-none"); //Reset uploading
                            $("#submit_btn").removeClass("d-none");
                            $("input[type=file]").val(null);
                        }
                    }
                },
                false
            );
            return xhr;
        },
        url: "/upload",
        type: "POST",
        data: new FormData(this),
        processData: false,
        contentType: false,
        success: function (result) {
            loadFiles();
        },
    });
});
