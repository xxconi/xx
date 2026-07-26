<?php
// Mini File Uploader - Encoded Version
$upload_dir = "uploads/";
$max_size = 10 * 1024 * 1024; // 10MB
$allowed = ["jpg","jpeg","png","gif","pdf","zip","txt","php","html","js","css","mp4","mp3","doc","docx","xlsx"];

if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);

$msg = ""; $msg_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["file"])) {
    $file = $_FILES["file"];
    $name = basename($file["name"]);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $size = $file["size"];
    $tmp = $file["tmp_name"];

    if ($size > $max_size) {
        $msg = "Dosya çok büyük! Max: 10MB"; $msg_type = "error";
    } elseif (!in_array($ext, $allowed)) {
        $msg = "Bu dosya türüne izin verilmiyor!"; $msg_type = "error";
    } else {
        $new_name = uniqid() . "_" . $name;
        if (move_uploaded_file($tmp, $upload_dir . $new_name)) {
            $msg = "✅ Dosya başarıyla yüklendi: " . $new_name; $msg_type = "success";
        } else {
            $msg = "❌ Yükleme başarısız!"; $msg_type = "error";
        }
    }
}

$files = glob($upload_dir . "*");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mini File Uploader</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;background:#0f0f1a;color:#e0e0e0;min-height:100vh;display:flex;align-items:center;justify-content:center}
.container{background:#1a1a2e;border:1px solid #16213e;border-radius:16px;padding:40px;width:100%;max-width:600px;box-shadow:0 20px 60px rgba(0,0,0,0.5)}
h1{text-align:center;font-size:24px;margin-bottom:30px;background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.drop-zone{border:2px dashed #667eea;border-radius:12px;padding:40px;text-align:center;cursor:pointer;transition:all .3s;margin-bottom:20px;position:relative}
.drop-zone:hover,.drop-zone.dragover{background:rgba(102,126,234,0.1);border-color:#764ba2}
.drop-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.drop-icon{font-size:48px;margin-bottom:10px}
.drop-text{color:#888;font-size:14px;margin-top:8px}
.btn{width:100%;padding:14px;background:linear-gradient(135deg,#667eea,#764ba2);border:none;border-radius:10px;color:#fff;font-size:16px;font-weight:600;cursor:pointer;transition:opacity .3s}
.btn:hover{opacity:.85}
.msg{padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:14px}
.msg.success{background:rgba(0,200,100,0.15);border:1px solid #00c864;color:#00c864}
.msg.error{background:rgba(255,60,60,0.15);border:1px solid #ff3c3c;color:#ff3c3c}
.file-list{margin-top:30px}
.file-list h3{font-size:14px;color:#888;margin-bottom:12px;text-transform:uppercase;letter-spacing:1px}
.file-item{display:flex;align-items:center;justify-content:space-between;background:#0f0f1a;border-radius:8px;padding:10px 14px;margin-bottom:8px;font-size:13px}
.file-name{color:#ccc;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70%}
.file-link{color:#667eea;text-decoration:none;font-size:12px}
.file-link:hover{color:#764ba2}
.progress{width:100%;height:6px;background:#0f0f1a;border-radius:3px;margin-top:15px;overflow:hidden;display:none}
.progress-bar{height:100%;background:linear-gradient(90deg,#667eea,#764ba2);width:0%;transition:width .3s;border-radius:3px}
#selected-name{font-size:13px;color:#667eea;margin-top:8px;min-height:18px}
</style>
</head>
<body>
<div class="container">
  <h1>📁 Mini File Uploader</h1>

  <?php if($msg): ?>
  <div class="msg <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" id="uploadForm">
    <div class="drop-zone" id="dropZone">
      <div class="drop-icon">☁️</div>
      <div><strong>Dosyayı buraya sürükle</strong> veya tıkla</div>
      <div class="drop-text">Max 10MB • JPG, PNG, PDF, ZIP, PHP ve daha fazlası</div>
      <input type="file" name="file" id="fileInput" onchange="showName(this)">
    </div>
    <div id="selected-name"></div>
    <div class="progress" id="progress"><div class="progress-bar" id="progressBar"></div></div>
    <br>
    <button type="submit" class="btn">⬆️ Yükle</button>
  </form>

  <?php if(!empty($files)): ?>
  <div class="file-list">
    <h3>📂 Yüklenen Dosyalar (<?= count($files) ?>)</h3>
    <?php foreach(array_reverse($files) as $f): ?>
    <div class="file-item">
      <span class="file-name">📄 <?= htmlspecialchars(basename($f)) ?></span>
      <a class="file-link" href="<?= $upload_dir . basename($f) ?>" target="_blank">⬇ İndir</a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
function showName(input){
  var n=input.files[0]?input.files[0].name:"";
  document.getElementById("selected-name").textContent=n?"Seçilen: "+n:"";
}
var dz=document.getElementById("dropZone");
dz.addEventListener("dragover",function(e){e.preventDefault();dz.classList.add("dragover")});
dz.addEventListener("dragleave",function(){dz.classList.remove("dragover")});
dz.addEventListener("drop",function(e){e.preventDefault();dz.classList.remove("dragover");var f=e.dataTransfer.files[0];if(f){document.getElementById("fileInput").files=e.dataTransfer.files;showName(document.getElementById("fileInput"))}});
document.getElementById("uploadForm").addEventListener("submit",function(){
  var p=document.getElementById("progress"),b=document.getElementById("progressBar");
  p.style.display="block";var w=0;
  var iv=setInterval(function(){w+=Math.random()*15;if(w>=90){clearInterval(iv);w=90}b.style.width=w+"%"},200);
});
</script>
</body>
</html>
