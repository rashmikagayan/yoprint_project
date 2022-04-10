<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap -->
    <link href=https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css rel=stylesheet>
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>

<body class="antialiased">
    <div class="p-5">
        <h2>Upload your file here!</h2>
        <form id="file_upload_form" method="POST" action="{{ url('upload') }}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group d-flex">
                <input class="form-control" type="file" name="file" id="file" required accept=".csv">
                <div class="form-group">
                    <button id="submit_btn" type="submit" class="btn btn-primary" name="submit">Upload</button>
                    <button class="btn btn-primary  d-none" id="loading_btn" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </button>
                </div>
            </div>
        </form>
        {{-- Progress Bar --}}
        <div id="progress_wrapper" class="d-none">
            <label id="progress_status">10% Uploaded</label>
            <div class="progress">
                <div id="progress" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
        <div id="alert_wrapper"></div>

        <table id="data_table" cellspacing=0 class="table table-bordered table-hover table-inverse table-striped" width=100%>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>File Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <!-- JavaScript Bundle with Popper -->
    <script src=https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js></script>

    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            });
            loadFiles();
        });

        function loadFiles() {
            $.ajax({
                type: 'GET',
                url: "/refresh",
                success: function() {}
            });
        }


        window.Echo.channel(`action-channel-one`).listen("ActionEvent", (response) => {
            $("#data_table tbody").empty();
            response.batchData.forEach(data => {
                var currentTime = new Date(data.CreatedAt).toLocaleString();
                appendToTable(currentTime, data.FileName, data.Status);
            });
        });

        function appendToTable(createdAt, FileName, Status) {
            var markup = "<tr><td>" + createdAt + "</td><td>" + FileName + "</td><td>" + Status + "</td></tr>";
            $("#data_table tbody").prepend(markup);
        }

        // Notify alert
        function showAlert(message, alert) {
            $('#alert_wrapper').html(
                `<div class="alert alert-${alert} alert-dismissible fade show" role="alert">
                    <span>${message}</span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    </div>`
            );
        }

        $('#file_upload_form').submit(
            function(e) {
                e.preventDefault();
                $('#progress_wrapper').removeClass('d-none'); //Show progress bar
                $('#submit_btn').addClass('d-none'); //Hide upload btn
                $('#loading_btn').removeClass('d-none'); //Show Uploading
                $.ajax({
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = ((evt.loaded / evt.total) * 100).toFixed(0);
                                $('#progress').css('width', percentComplete + '%').attr('aria-valuenow', percentComplete); //Show progress
                                $("#progress_status").html(`${percentComplete} % Uploaded`);
                                if (percentComplete == 100) {
                                    $('#progress_wrapper').addClass('d-none'); //Show progress bar
                                    var currentTime = new Date().toLocaleString();
                                    var fileName = $('input[type=file]').val().split('\\').pop().split('.').shift();
                                    showAlert('Preparing file for process', 'info')
                                    appendToTable(currentTime, fileName, 'Pending');
                                    $('#loading_btn').addClass('d-none'); //Reset uploading
                                    $('#submit_btn').removeClass('d-none');
                                    $('input[type=file]').val(null);
                                }
                            }
                        }, false);
                        return xhr;
                    },
                    url: '/upload',
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        loadFiles();
                    }
                });
            }
        );
    </script>
</body>

</html>