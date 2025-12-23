<?php
// Türkiye Saati Ayarı
date_default_timezone_set('Europe/Istanbul');

$hn = 'localhost';
$db = 'votepool_db';
$un = 'root';
$pw = ''; 

$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die("Veritabanı Bağlantı Hatası");

// İŞTE EKSİK OLAN FONKSİYON BU:
function sanitize($conn, $str) {
    return htmlentities(strip_tags(stripslashes($str)));
}
?>