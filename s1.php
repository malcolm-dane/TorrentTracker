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
    if (array_key_exists('user', $_SESSION)) {
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
      <script id="rendered-js">

const gui = new dat.GUI({ closed: true, width: 350 });

const parameters = {
  count: 250000,
  radius: 5,
  branches: 5,
  spin: 1,
  randomness: 0.8,
  randomnessPower: 4,
  insideColor: "#ec5300",
  outsideColor: "#2fb4fc" };


const canvas = document.querySelector("canvas.webgl");

// Scene
const scene = new THREE.Scene();

// TextureLoader
const textureLoader = new THREE.TextureLoader();
const starTexture = textureLoader.load(
"https://assets.codepen.io/22914/star_02.png");



let geometry = null;
let material = null;
let points = null;

const generateGalaxy = () => {
  if (points !== null) {
    geometry.dispose();
    material.dispose();
    scene.remove(points);
  }


  geometry = new THREE.BufferGeometry();

  const positions = new Float32Array(parameters.count * 3);
  const colors = new Float32Array(parameters.count * 3);
  const scales = new Float32Array(parameters.count);
  const randomness = new Float32Array(parameters.count * 3);
  const insideColor = new THREE.Color(parameters.insideColor);
  const outsideColor = new THREE.Color(parameters.outsideColor);

  for (let i = 0; i < parameters.count; i++) {
    const i3 = i * 3;

    // Position
    const radius = Math.random() * parameters.radius;

    const branchAngle =
    i % parameters.branches / parameters.branches * Math.PI * 2;

    const randomX =
    Math.pow(Math.random(), parameters.randomnessPower) * (
    Math.random() < 0.5 ? 1 : -1) *
    parameters.randomness *
    radius;
    const randomY =
    Math.pow(Math.random(), parameters.randomnessPower) * (
    Math.random() < 0.5 ? 1 : -1) *
    parameters.randomness *
    radius;
    const randomZ =
    Math.pow(Math.random(), parameters.randomnessPower) * (
    Math.random() < 0.5 ? 1 : -1) *
    parameters.randomness *
    radius;

    positions[i3] = Math.cos(branchAngle) * radius;
    positions[i3 + 1] = 0;
    positions[i3 + 2] = Math.sin(branchAngle) * radius;

    // Randomness
    randomness[i3] = randomX;
    randomness[i3 + 1] = randomY;
    randomness[i3 + 2] = randomZ;

    // Color
    const mixedColor = insideColor.clone();
    mixedColor.lerp(outsideColor, radius / parameters.radius);

    colors[i3] = mixedColor.r;
    colors[i3 + 1] = mixedColor.g;
    colors[i3 + 2] = mixedColor.b;

    // Scales
    scales[i] = Math.random();
  }

  geometry.setAttribute("position", new THREE.BufferAttribute(positions, 3));
  geometry.setAttribute("color", new THREE.BufferAttribute(colors, 3));
  geometry.setAttribute("aScale", new THREE.BufferAttribute(scales, 1));
  geometry.setAttribute(
  "aRandomness",
  new THREE.BufferAttribute(randomness, 3));

  // console.log(new THREE.)
  /**
   * Material
   */
  material = new THREE.ShaderMaterial({
    depthWrite: false,
    blending: THREE.AdditiveBlending,
    vertexColors: true,
    vertexShader: document.getElementById("vertexShader").textContent,
    fragmentShader: document.getElementById("fragmentShader").textContent,
    transparent: true,
    uniforms: {
      uTime: { value: 0 },
      uSize: { value: 30 * renderer.getPixelRatio() },
      uHoleSize: { value: 0.15 },
      uTexture: { value: starTexture },
      size: { value: 1.0 } } });




  points = new THREE.Points(geometry, material);
  scene.add(points);
};

gui.
add(parameters, "count").
min(100).
max(1000000).
step(100).
onFinishChange(generateGalaxy).
name("Star count");
gui.
add(parameters, "radius").
min(0.01).
max(20).
step(0.01).
onFinishChange(generateGalaxy).
name("Galaxy radius");
gui.
add(parameters, "branches").
min(2).
max(20).
step(1).
onFinishChange(generateGalaxy).
name("Galaxy branches");
gui.
add(parameters, "randomness").
min(0).
max(2).
step(0.001).
onFinishChange(generateGalaxy).
name("Randomness position");
gui.
add(parameters, "randomnessPower").
min(1).
max(10).
step(0.001).
onFinishChange(generateGalaxy).
name("Randomness power");
gui.
addColor(parameters, "insideColor").
onFinishChange(generateGalaxy).
name("Galaxy inside color");
gui.
addColor(parameters, "outsideColor").
onFinishChange(generateGalaxy).
name("Galaxy outside color");

/**
 * Sizes
 */
const sizes = {
  width: window.innerWidth,
  height: window.innerHeight };


window.addEventListener("resize", () => {
  // Update sizes
  sizes.width = window.innerWidth;
  sizes.height = window.innerHeight;

  // Update camera
  camera.aspect = sizes.width / sizes.height;
  camera.updateProjectionMatrix();

  // Update renderer
  renderer.setSize(sizes.width, sizes.height);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
});

/**
 * Camera
 */
// Base camera
const camera = new THREE.PerspectiveCamera(
75,
sizes.width / sizes.height,
0.1,
100);

camera.position.x = 3;
camera.position.y = 3;
camera.position.z = 3;
scene.add(camera);

// Controls
const controls = new THREE.OrbitControls(camera, canvas);
controls.enableDamping = true;

/**
 * Renderer
 */
const renderer = new THREE.WebGLRenderer({
  canvas: canvas });

renderer.setSize(sizes.width, sizes.height);
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

generateGalaxy();

gui.
add(material.uniforms.uSize, "value").
min(1).
max(100).
step(0.001).
name("Point size").
onChange(() => {
  material.uniforms.uSize.value =
  material.uniforms.uSize.value * renderer.getPixelRatio();
});

gui.
add(material.uniforms.uHoleSize, "value").
min(0).
max(1).
step(0.001).
name("Black hole size");

/**
 * Animate
 */
const clock = new THREE.Clock();

const tick = () => {
  const elapsedTime = clock.getElapsedTime();

  material.uniforms.uTime.value = elapsedTime;

  // Update controls
  controls.update();

  // Render
  renderer.render(scene, camera);

  // Call tick again on the next frame
  window.requestAnimationFrame(tick);
};

tick();
//# sourceURL=pen.js
    </script><div class="dg ac"><div class="dg main a" style="user-select: none; width: 350px;"><div style="width: 6px; margin-left: -3px; height: 0px; cursor: ew-resize; position: absolute;"></div><ul class="closed" style="height: auto;"><li class="cr number has-slider"><div><span class="property-name">Star count</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 24.9925%;"></div></div></div></div></li><li class="cr number has-slider"><div><span class="property-name">Galaxy radius</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 24.9625%;"></div></div></div></div></li><li class="cr number has-slider"><div><span class="property-name">Galaxy branches</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 16.6667%;"></div></div></div></div></li><li class="cr number has-slider"><div><span class="property-name">Randomness position</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 40%;"></div></div></div></div></li><li class="cr number has-slider"><div><span class="property-name">Randomness power</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 33.3333%;"></div></div></div></div></li><li class="cr color" style="border-left-color: rgb(236, 83, 0);"><div><span class="property-name">Galaxy inside color</span><div style="user-select: none;" class="c"><input type="text" style="outline: none; text-align: center; color: rgb(255, 255, 255); border: 0px none; font-weight: bold; text-shadow: rgba(0, 0, 0, 0.7) 0px 1px 1px; background-color: rgb(236, 83, 0);"><div class="selector" style="width: 122px; height: 102px; padding: 3px; background-color: rgb(34, 34, 34); box-shadow: rgba(0, 0, 0, 0.3) 0px 1px 3px;"><div class="field-knob" style="position: absolute; width: 12px; height: 12px; border: 2px solid rgb(255, 255, 255); box-shadow: rgba(0, 0, 0, 0.5) 0px 1px 3px; border-radius: 12px; z-index: 1; margin-left: 93px; margin-top: 0.45098px; background-color: rgb(236, 83, 0);"></div><div class="saturation-field" style="width: 100px; height: 100px; border: 1px solid rgb(85, 85, 85); margin-right: 3px; display: inline-block; cursor: pointer; background: -webkit-linear-gradient(left, rgb(255, 255, 255) 0%, rgb(255, 89, 0) 100%);"><div style="width: 100%; height: 100%; background: -webkit-linear-gradient(rgba(0, 0, 0, 0) 0%, rgb(0, 0, 0) 100%);"></div></div><div class="hue-field" style="width: 15px; height: 100px; border: 1px solid rgb(85, 85, 85); cursor: ns-resize; position: absolute; top: 3px; right: 3px; background: -webkit-linear-gradient(rgb(255, 0, 0) 0%, rgb(255, 0, 255) 17%, rgb(0, 0, 255) 34%, rgb(0, 255, 255) 50%, rgb(0, 255, 0) 67%, rgb(255, 255, 0) 84%, rgb(255, 0, 0) 100%);"><div class="hue-knob" style="position: absolute; width: 15px; height: 2px; border-right: 4px solid rgb(255, 255, 255); z-index: 1; margin-top: 94.1384px;"></div></div></div></div></div></li><li class="cr color" style="border-left-color: rgb(47, 180, 252);"><div><span class="property-name">Galaxy outside color</span><div style="user-select: none;" class="c"><input type="text" style="outline: none; text-align: center; color: rgb(255, 255, 255); border: 0px none; font-weight: bold; text-shadow: rgba(0, 0, 0, 0.7) 0px 1px 1px; background-color: rgb(47, 180, 252);"><div class="selector" style="width: 122px; height: 102px; padding: 3px; background-color: rgb(34, 34, 34); box-shadow: rgba(0, 0, 0, 0.3) 0px 1px 3px;"><div class="field-knob" style="position: absolute; width: 12px; height: 12px; border: 2px solid rgb(255, 255, 255); box-shadow: rgba(0, 0, 0, 0.5) 0px 1px 3px; border-radius: 12px; z-index: 1; margin-left: 74.3492px; margin-top: -5.82353px; background-color: rgb(47, 180, 252);"></div><div class="saturation-field" style="width: 100px; height: 100px; border: 1px solid rgb(85, 85, 85); margin-right: 3px; display: inline-block; cursor: pointer; background: -webkit-linear-gradient(left, rgb(255, 255, 255) 0%, rgb(0, 165, 255) 100%);"><div style="width: 100%; height: 100%; background: -webkit-linear-gradient(rgba(0, 0, 0, 0) 0%, rgb(0, 0, 0) 100%);"></div></div><div class="hue-field" style="width: 15px; height: 100px; border: 1px solid rgb(85, 85, 85); cursor: ns-resize; position: absolute; top: 3px; right: 3px; background: -webkit-linear-gradient(rgb(255, 0, 0) 0%, rgb(255, 0, 255) 17%, rgb(0, 0, 255) 34%, rgb(0, 255, 255) 50%, rgb(0, 255, 0) 67%, rgb(255, 255, 0) 84%, rgb(255, 0, 0) 100%);"><div class="hue-knob" style="position: absolute; width: 15px; height: 2px; border-right: 4px solid rgb(255, 255, 255); z-index: 1; margin-top: 44.1463px;"></div></div></div></div></div></li><li class="cr number has-slider"><div><span class="property-name">Point size</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 29.2929%;"></div></div></div></div></li><li class="cr number has-slider"><div><span class="property-name">Black hole size</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 15%;"></div></div></div></div></li></ul><div class="close-button close-bottom" style="width: 350px;">Close Controls</div></div></div><div class="dg ac" style="display:none"><div class="dg main a" style="user-select: none; width: 350px;"><div style="width: 6px; margin-left: -3px; height: 0px; cursor: ew-resize; position: absolute;"></div><ul class="closed" style="height: auto;"><li class="cr number has-slider"><div><span class="property-name">Star count</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 24.9925%;"></div></div></div></div></li><li class="cr number has-slider"><div><span class="property-name">Galaxy radius</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 24.9625%;"></div></div></div></div></li><li class="cr number has-slider"><div><span class="property-name">Galaxy branches</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 16.6667%;"></div></div></div></div></li><li class="cr number has-slider"><div><span class="property-name">Randomness position</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 40%;"></div></div></div></div></li><li class="cr number has-slider"><div><span class="property-name">Randomness power</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 33.3333%;"></div></div></div></div></li><li class="cr color" style="border-left-color: rgb(236, 83, 0);"><div><span class="property-name">Galaxy inside color</span><div style="user-select: none;" class="c"><input type="text" style="outline: none; text-align: center; color: rgb(255, 255, 255); border: 0px none; font-weight: bold; text-shadow: rgba(0, 0, 0, 0.7) 0px 1px 1px; background-color: rgb(236, 83, 0);"><div class="selector" style="width: 122px; height: 102px; padding: 3px; background-color: rgb(34, 34, 34); box-shadow: rgba(0, 0, 0, 0.3) 0px 1px 3px;"><div class="field-knob" style="position: absolute; width: 12px; height: 12px; border: 2px solid rgb(255, 255, 255); box-shadow: rgba(0, 0, 0, 0.5) 0px 1px 3px; border-radius: 12px; z-index: 1; margin-left: 93px; margin-top: 0.45098px; background-color: rgb(236, 83, 0);"></div><div class="saturation-field" style="width: 100px; height: 100px; border: 1px solid rgb(85, 85, 85); margin-right: 3px; display: inline-block; cursor: pointer; background: -webkit-linear-gradient(left, rgb(255, 255, 255) 0%, rgb(255, 89, 0) 100%);"><div style="width: 100%; height: 100%; background: -webkit-linear-gradient(rgba(0, 0, 0, 0) 0%, rgb(0, 0, 0) 100%);"></div></div><div class="hue-field" style="width: 15px; height: 100px; border: 1px solid rgb(85, 85, 85); cursor: ns-resize; position: absolute; top: 3px; right: 3px; background: -webkit-linear-gradient(rgb(255, 0, 0) 0%, rgb(255, 0, 255) 17%, rgb(0, 0, 255) 34%, rgb(0, 255, 255) 50%, rgb(0, 255, 0) 67%, rgb(255, 255, 0) 84%, rgb(255, 0, 0) 100%);"><div class="hue-knob" style="position: absolute; width: 15px; height: 2px; border-right: 4px solid rgb(255, 255, 255); z-index: 1; margin-top: 94.1384px;"></div></div></div></div></div></li><li class="cr color" style="border-left-color: rgb(47, 180, 252);"><div><span class="property-name">Galaxy outside color</span><div style="user-select: none;" class="c"><input type="text" style="outline: none; text-align: center; color: rgb(255, 255, 255); border: 0px none; font-weight: bold; text-shadow: rgba(0, 0, 0, 0.7) 0px 1px 1px; background-color: rgb(47, 180, 252);"><div class="selector" style="width: 122px; height: 102px; padding: 3px; background-color: rgb(34, 34, 34); box-shadow: rgba(0, 0, 0, 0.3) 0px 1px 3px;"><div class="field-knob" style="position: absolute; width: 12px; height: 12px; border: 2px solid rgb(255, 255, 255); box-shadow: rgba(0, 0, 0, 0.5) 0px 1px 3px; border-radius: 12px; z-index: 1; margin-left: 74.3492px; margin-top: -5.82353px; background-color: rgb(47, 180, 252);"></div><div class="saturation-field" style="width: 100px; height: 100px; border: 1px solid rgb(85, 85, 85); margin-right: 3px; display: inline-block; cursor: pointer; background: -webkit-linear-gradient(left, rgb(255, 255, 255) 0%, rgb(0, 165, 255) 100%);"><div style="width: 100%; height: 100%; background: -webkit-linear-gradient(rgba(0, 0, 0, 0) 0%, rgb(0, 0, 0) 100%);"></div></div><div class="hue-field" style="width: 15px; height: 100px; border: 1px solid rgb(85, 85, 85); cursor: ns-resize; position: absolute; top: 3px; right: 3px; background: -webkit-linear-gradient(rgb(255, 0, 0) 0%, rgb(255, 0, 255) 17%, rgb(0, 0, 255) 34%, rgb(0, 255, 255) 50%, rgb(0, 255, 0) 67%, rgb(255, 255, 0) 84%, rgb(255, 0, 0) 100%);"><div class="hue-knob" style="position: absolute; width: 15px; height: 2px; border-right: 4px solid rgb(255, 255, 255); z-index: 1; margin-top: 44.1463px;"></div></div></div></div></div></li><li class="cr number has-slider"><div><span class="property-name">Point size</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 29.2929%;"></div></div></div></div></li><li class="cr number has-slider"><div><span class="property-name">Black hole size</span><div class="c"><div><input type="text"></div><div class="slider"><div class="slider-fg" style="width: 15%;"></div></div></div></div></li></ul><div class="close-button close-bottom" style="width: 350px;">Close Controls</div></div></div> <link rel="stylesheet" media="all" href="https://engine.thenewmanagementinc.com/assets/application-2c251ffd51eaab78f0d578c164b7dde4b0debf7d0141761e5544589c5f2955c6.css">
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
