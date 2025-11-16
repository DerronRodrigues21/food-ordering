<?php
// CLI usage: php tools/resize_images.php
// Browser usage: http://localhost/yourproject/tools/resize_images.php

$dir = __DIR__ . '/../images';
$w = 600; $h = 360;       // restaurant image size
$thumb_w = 480; $thumb_h = 320; // item image size

function crop_resize($src, $dst, $tw, $th) {
    $info = getimagesize($src);
    if (!$info) return false;
    $mime = $info['mime'];
    if ($mime === 'image/jpeg') $img = imagecreatefromjpeg($src);
    elseif ($mime === 'image/png') $img = imagecreatefrompng($src);
    else return false;

    $sw = imagesx($img); 
    $sh = imagesy($img);
    $src_ratio = $sw/$sh;
    $tar_ratio = $tw/$th;

    if ($src_ratio > $tar_ratio) {
        $new_w = (int)($sh * $tar_ratio);
        $new_h = $sh;
        $sx = (int)(($sw - $new_w)/2);
        $sy = 0;
    } else {
        $new_w = $sw;
        $new_h = (int)($sw / $tar_ratio);
        $sx = 0;
        $sy = (int)(($sh - $new_h)/2);
    }

    $dst_img = imagecreatetruecolor($tw,$th);
    imagecopyresampled($dst_img,$img,0,0,$sx,$sy,$tw,$th,$new_w,$new_h);

    imagejpeg($dst_img,$dst,85);
    imagedestroy($img);
    imagedestroy($dst_img);
    return true;
}

$files = scandir($dir);
foreach($files as $f){
    if (preg_match('/restaurant_(\d+)\.(jpg|jpeg|png)$/i',$f,$m)){
        $src = $dir.'/'.$f;
        $dst = $dir.'/restaurant_'.$m[1].'.jpg';
        crop_resize($src,$dst,$w,$h);
        echo "Processed $f -> restaurant_{$m[1]}.jpg\n";
    }
    if (preg_match('/item_(\d+)\.(jpg|jpeg|png)$/i',$f,$m)){
        $src = $dir.'/'.$f;
        $dst = $dir.'/item_'.$m[1].'.jpg';
        crop_resize($src,$dst,$thumb_w,$thumb_h);
        echo "Processed $f -> item_{$m[1]}.jpg\n";
    }
}
echo "\nDone.\n";
?>
