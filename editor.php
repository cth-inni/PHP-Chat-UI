<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Hello, world!</title>
    <style>
        #textarea-rawscript {
            width: 100%;
            height: 100%;
            padding: 10px;
        }
    </style>
</head>

<body>
    <h1>Editor (alpha)</h1>
    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <div class="container">
        <div class="row">
            <div class="col-1">
                Tools
            </div>
            <div class="col-6">
                <?php
                $content = $_POST['rawscript'];

                echo '<textarea id="textarea-rawscript" class="form-control" rows="15" placeholder="Rawscript">';
                echo $content;
                echo '</textarea>';

                ?>
            </div>
            <div class="col">
                Preview
            </div>
        </div>
    </div>
</body>

</html>