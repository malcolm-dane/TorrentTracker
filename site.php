<?php

require_once 'config.php';
require_once 'db.php';
$db = $CONFIG['db']['type'] == 'mysql' ? new MySqlDatabase()
                                       : new PostgreSqlDatabase();

function html_escape($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function random_hash() {
    $s = openssl_random_pseudo_bytes(30);
    if ($s === null) {
        die('no source of randomness');
    }

    return md5($s);
}

function require_auth() {
    global $CONFIG;
    if (!array_key_exists('user', $_SESSION)) {
        header(sprintf('Location: %s/login.php', $CONFIG['base_url']));
        die;
    }
}

function check_csrf() {
    if (!array_key_exists('csrf', $_POST) || $_POST['csrf'] !== $_SESSION['csrf']) {
        die;
    }
}

function csrf_html() {
    printf('<input type="hidden", name="csrf" value="%s" />', html_escape($_SESSION['csrf']));
}

function gen_csrf($replace = false) {
    if ($replace || !array_key_exists('csrf', $_SESSION)) {
        $_SESSION['csrf'] = random_hash();
    }
}

function format_size($b) {
    if ($b < 1024) return round($b,2) . 'B';
    $b /= 1024.0;
    if ($b < 1024) return round($b,2) . 'KiB';
    $b /= 1024.0;
    if ($b < 1024) return round($b,2) . 'MiB';
    $b /= 1024.0;
    if ($b < 1024) return round($b,2) . 'GiB';
    $b /= 1024.0;
    return round($b,2) . 'TiB';
}

function site_header() {
    global $CONFIG;
    printf('<!DOCTYPE html>');
    printf('<html>');
    printf('<head>');
    printf('<meta name="viewport" content="width=device-width, initial-scale=1">');
    printf('<meta name="format-detection" content="telephone=no">');
    printf('<link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">');
    printf('<link rel="icon" href="/img/favicon.ico" type="image/x-icon">');
    printf('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">');
    printf('<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>');
    printf('<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>');
    printf('<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>');
      printf('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">');
    printf('<link rel="stylesheet" href="https://foodtoken.club/simpletracker/af.css">');

printf('<style>* {margin: 0; padding: 0; } html, body {overflow: hidden; } .webgl {position: fixed; top: 0; left: 0; outline: none; } html body div.dg.ac{display:none} </style>');



    printf('<title>%s</title>', html_escape($CONFIG['site_title']));
    printf('</head>');
    printf('<body>');


        ?>
         
<script id="vertexShader" type="x-shader/x-vertex">
  uniform float uSize;
uniform float uTime;
uniform float uHoleSize;

attribute float aScale;
attribute vec3 aRandomness;

varying vec3 vColor;

void main() {
  vec4 modelPosition = modelMatrix * vec4(position, 1.0);
  
  // Spin
  float angle = atan(modelPosition.x, modelPosition.z);
  float distanceToCenter = length(modelPosition.xz) + uHoleSize;
  float uTimeOffset = uTime + (uHoleSize * 30.0);
  float angleOffset = (1.0 / distanceToCenter) * uTimeOffset * 0.2;
  angle += angleOffset;
  
  modelPosition.x = cos(angle) * distanceToCenter;
  modelPosition.z = sin(angle) * distanceToCenter;  
  modelPosition.xyz += aRandomness; 

  vec4 viewPosition = viewMatrix * modelPosition;
  vec4 projectedPosition = projectionMatrix * viewPosition;


  gl_Position = projectedPosition; 
  float scale = uSize * aScale;
  
  gl_PointSize = scale;
  gl_PointSize *= ( 1.0 / - viewPosition.z );
  vColor = color;
}
</script>
<script id="fragmentShader" type="x-shader/x-fragment">
  varying vec3 vColor;
varying vec2 vUv;
uniform sampler2D uTexture;

void main() {
  gl_FragColor = vec4( vColor, 1.0 );
  gl_FragColor = gl_FragColor * texture2D(uTexture, vec2( gl_PointCoord.x, gl_PointCoord.y ) );
  gl_FragColor = gl_FragColor * vec4( vColor, 1.0 );
}
</script>
<canvas class="webgl" style="width: 1920px; height: 508px;" width="1920" height="508"></canvas>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r125/three.min.js"></script>
<script src="https://unpkg.com/three@0.125.2/examples/js/controls/OrbitControls.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dat-gui/0.7.7/dat.gui.min.js"></script>
<script src="https://thenewmanagementinc.com/tor/serve/render.js"></script>
<link rel="stylesheet" media="all" href="https://engine.thenewmanagementinc.com/assets/application-2c251ffd51eaab78f0d578c164b7dde4b0debf7d0141761e5544589c5f2955c6.css">


<style type="text/css">#box{overflow:auto} #win{overflow: auto;} #winb{overflow:auto} #winb{overflow: auto;}#win.winb {overflow: auto}; .navbar-light .navbar-nav .nav-link{color:#0ff}</style>
        <nav style="background-color:#3a40464f" class="navbar navbar-expand-lg navbar-light">
  <a style="color:#0ff" class="navbar-brand" href="#"><?php echo $CONFIG['site_title']; ?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a style="color:#0ff" class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a style="color:#0ff" class="nav-link" href="upload.php">Upload</a>
      </li>
      <li class="nav-item">
        <a style="color:#0ff" class="nav-link" href="invitations.php">Invite</a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <?php echo $_SESSION['user']['username']; ?>
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a style="color:#0ff" href="#">Account</a>
          <div class="dropdown-divider"></div>
          <a style="color:#0ff" href="logout.php" class="dropdown-item">Log Out</a>
        </div>
      </li>
    </ul>
    <!--
    <form class="form-inline my-2 my-lg-0">
      <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" disabled>
      <button class="btn btn-outline-success my-2 my-sm-0" type="submit" disabled>Search (Coming Soon)</button>
    </form>-->
  </div>
</nav>
<section id="win">
    <style>#box {
    overflow: scroll; height: 900px;max-height: 100%;
}</style>
<div id="box" style="overflow: scroll; height: 900px;max-height: 100%;">
       <div class="container">
    <?php
    }


function site_footer() {
    ?>
    </div>
   <div id="win">


      <div id="winb" style='display:none' >
        

<h2></h2>
<table style="
    overflow-y: scroll;
    height: 824px;
    max-height: 100%;
    display: flow-root;">
  <thead>
      <tr><th colspan="3"><h2></h2></th></tr>
  </thead>
  
</table>

  <p></p>
<br>
<h2></h2>
<p></p>
<pre></pre>
<small><b>Web3 powered by blockchain</b></small>

        <br>
        <div id="foot" style="overflow:auto;">
  
        </div>
      </div>
    </div>
  </div>
</section>


    </body>
    <?php
}


// session setup
session_start();
gen_csrf();
