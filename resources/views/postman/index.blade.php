<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laravel API Tester</title>
    <link href="{{ url('assets/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            background-color: #0d1117;
            color: #c9d1d9;
        }

        .form-control, .form-select, textarea {
            background-color: #161b22;
            color: #58a6ff;
            border: 1px solid #30363d;
        }

        .form-control:focus, .form-select:focus, textarea:focus {
            background-color: #161b22;
            color: #58a6ff;
            border-color: #58a6ff;
            box-shadow: 0 0 5px #58a6ff;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            box-shadow: 0 0 8px #58a6ff;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        pre {
            background-color: #161b22;
            color: #58a6ff;
            border: 1px solid #30363d;
        }

        label {
            color: #8b949e;
        }

        h2, h5 {
            color: #58a6ff;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4">Laravel API Tester</h2>
    <div class="row">
        <div class="col-md-6">
            <form id="apiForm">
                <div class="mb-3">
                    <label for="url" class="form-label">Request URL</label>
                    <input type="text" name="url" class="form-control requestURL" id="url" required value="">
                </div>

                <div class="mb-3">
                    <label for="method" class="form-label">HTTP Method</label>
                    <select name="method" class="form-select httpMethod" id="method">
                        <option value="get">GET</option>
                        <option value="post">POST</option>
                        <option value="put">PUT</option>
                        <option value="delete">DELETE</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="token" class="form-label">Bearer Token (Optional)</label>
                    <input type="text" name="token" class="form-control bearerToken" id="token" placeholder="Paste your token here if needed">
                </div>

                <div class="mb-3">
                    <label for="body" class="form-label">JSON Body</label>
                    <textarea name="body" class="form-control jsonBody" id="body" rows="6"></textarea>
                </div>

                <button type="submit" class="btn btn-primary sendRequest">Send Request</button>
            </form>
        </div>
        <div class="col-md-6">
            <h5>Response:</h5>
            <pre id="responseOutput" class="p-3 rounded" style="min-height: 200px;"></pre>
        </div>
    </div>
</div>

<script src="{{ url('assets/jquery/jquery.min.js') }}"></script>
<script>
    $(document).ready(function () {
        let laravelAPITesterLocalStorage = JSON.parse(localStorage.getItem('laravelAPITester'));

        if (!laravelAPITesterLocalStorage) {
            laravelAPITesterLocalStorage = {
                requestURL: "",
                httpMethod: "",
                bearerToken: "",
                jsonBodyRaw: "",
                jsonBody: {}
            };
        }

        $('.requestURL').val(laravelAPITesterLocalStorage.requestURL);
        $('.httpMethod').val(laravelAPITesterLocalStorage.httpMethod);
        $('.bearerToken').val(laravelAPITesterLocalStorage.bearerToken);
        $('.jsonBody').val(laravelAPITesterLocalStorage.jsonBodyRaw);

        $('.sendRequest').click(function (e) {
            e.preventDefault();

            let requestURL = $('.requestURL').val();
            let httpMethod = $('.httpMethod').val().toUpperCase();
            let bearerToken = $('.bearerToken').val();
            let jsonBodyRaw = $('.jsonBody').val();
            let jsonBody = {};

            // Store in localStorage
            laravelAPITesterLocalStorage.requestURL = requestURL;
            laravelAPITesterLocalStorage.httpMethod = httpMethod;
            laravelAPITesterLocalStorage.bearerToken = bearerToken;
            laravelAPITesterLocalStorage.jsonBodyRaw = jsonBodyRaw;
            laravelAPITesterLocalStorage.jsonBody = jsonBody;

            localStorage.setItem('laravelAPITester', JSON.stringify(laravelAPITesterLocalStorage));

            try {
                if (httpMethod !== 'GET' && jsonBodyRaw.trim() !== '') {
                    jsonBody = JSON.parse(jsonBodyRaw);
                }
            } catch (err) {
                alert("Invalid JSON body!");
                return;
            }

            $.ajax({
                url: requestURL,
                type: httpMethod,
                headers: bearerToken ? {
                    'Authorization': 'Bearer ' + bearerToken,
                    'Content-Type': 'application/json'
                } : {
                    'Content-Type': 'application/json'
                },
                data: httpMethod !== 'GET' ? JSON.stringify(jsonBody) : null,
                success: function (response) {
                    $('#responseOutput').text(JSON.stringify(response, null, 2));
                },
                error: function (xhr, status, error) {
                    let errorMsg = xhr.responseText || error;
                    $('#responseOutput').text("Error: " + errorMsg);
                }
            });
        });
    });
</script>
</body>
</html>
