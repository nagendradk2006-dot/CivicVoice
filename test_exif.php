<?php

$file = "Images/IMG_20260929_103132.jpg";

$exif = @exif_read_data($file, null, true);

echo "<pre>";

if (!$exif) {

    echo "NO EXIF DATA FOUND";

} else {

    echo "ALL EXIF DATA:\n\n";
    print_r($exif);

}

echo "</pre>";
?>