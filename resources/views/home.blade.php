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
</body>

</html>