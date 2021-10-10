<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
  <meta name="generator" content="Hugo 0.88.1">
  <title>Starter Template · Bootstrap v5.1</title>

  <link rel="canonical" href="https://getbootstrap.com/docs/5.1/examples/starter-template/">



  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <style>
    .bd-placeholder-img {
      font-size: 1.125rem;
      text-anchor: middle;
      -webkit-user-select: none;
      -moz-user-select: none;
      user-select: none;
    }

    @media (min-width: 768px) {
      .bd-placeholder-img-lg {
        font-size: 3.5rem;
      }
    }
  </style>
  <style>
    #textarea-rawscript {
      width: 100%;
      height: 100%;
      padding: 10px;
    }
    #output-result {
      overflow: auto;
      max-height: 400px;
      padding: 20px;
    }
  </style>

  <!-- Custom styles for this template -->
  <link href="starter-template.css" rel="stylesheet">
  <link href="headers.css" rel="stylesheet">
</head>

<body>

  <header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-grid gap-3 align-items-center" style="grid-template-columns: 1fr 2fr;">
      <div class="dropdown">
        <a href="#"
          class="d-flex align-items-center col-lg-4 mb-2 mb-lg-0 link-dark text-decoration-none dropdown-toggle"
          id="dropdownNavLink" data-bs-toggle="dropdown" aria-expanded="false">
          <svg class="bi me-2" width="40" height="32">
            <use xlink:href="#bootstrap" /></svg>
        </a>
        <ul class="dropdown-menu text-small shadow" aria-labelledby="dropdownNavLink">
          <li><a class="dropdown-item active" href="#" aria-current="page">Overview</a></li>
          <li><a class="dropdown-item" href="#">Inventory</a></li>
          <li><a class="dropdown-item" href="#">Customers</a></li>
          <li><a class="dropdown-item" href="#">Products</a></li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li><a class="dropdown-item" href="#">Reports</a></li>
          <li><a class="dropdown-item" href="#">Analytics</a></li>
        </ul>
      </div>

      <div class="d-flex align-items-center">
        <form class="w-100 me-3">
          <input type="search" class="form-control" placeholder="Search..." aria-label="Search">
        </form>

        <div class="flex-shrink-0 dropdown">
          <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="dropdownUser2"
            data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://github.com/mdo.png" alt="mdo" width="32" height="32" class="rounded-circle">
          </a>
          <ul class="dropdown-menu text-small shadow" aria-labelledby="dropdownUser2">
            <li><a class="dropdown-item" href="#">New project...</a></li>
            <li><a class="dropdown-item" href="#">Settings</a></li>
            <li><a class="dropdown-item" href="#">Profile</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#">Sign out</a></li>
          </ul>
        </div>
      </div>
    </div>
  </header>

  <div class="container-fluid pb-3">
    <div class="d-grid gap-3" style="grid-template-columns: 1fr 1fr;">
      <div class="bg-light border rounded-3">
        
<?php
$content = @$_POST['rawscript'];
echo '<textarea id="textarea-rawscript" class="form-control" rows="15" placeholder="Rawscript">';
if(is_null($content))
{
  echo $sample = file_get_contents('../sample/test.raw.2.txt');
}else{
  echo $content;
}
echo '</textarea>';
?>
      <div class="d-grid gap-2">
        <button id="action-btn-preview" class="btn btn-secondary" type="button">Preview</button>
      </div>
      </div>
      <div id="output-result" class="bg-light border rounded-3">
        <br><br><br><br><br><br><br><br><br><br>
      </div>
    </div>
  </div>


  <!-- Option 1: Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js" 
  integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  <script type="text/javascript">
  $(document).ready(function(){
    // setInterval(function(){ RefreshPreview(); }, 3000);
    $('#action-btn-preview').click(function(){
      RefreshPreview();
    });

    function RefreshPreview(){
      var previewContent = $('#textarea-rawscript').val();
      $.ajax({
        type: "POST",
        url: "../render.php?page=107",
        data: { rawscript : previewContent },
        dataType: "html",
        success: function(data){
          $( "#output-result" ).html( data );
        }
      });
    }
  });
  </script>
</body>
</html>