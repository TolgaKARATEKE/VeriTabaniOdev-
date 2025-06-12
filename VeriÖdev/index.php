<?php
session_start();
require_once 'config/database.php';


$page = isset($_GET['page']) ? $_GET['page'] : 'kontrol_paneli';


include 'includes/header.php';


switch($page) {
    case 'dashboard':
        include 'pages/dashboard.php';
        break;
    case 'musteriler':
        include 'pages/musteriler.php';
        break;
    case 'mustahsiller':
        include 'pages/mustahsiller.php';
        break;
    case 'urunler':
        include 'pages/urunler.php';
        break;
    case 'stok':
        include 'pages/stok.php';
        break;
    case 'satislar':
        include 'pages/satislar.php';
        break;
    case 'raporlar':
        include 'pages/raporlar.php';
        break;
    case 'kontrol_paneli':
        include 'pages/dashboard.php';
        break;
    default:
        include 'pages/dashboard.php';
}


include 'includes/footer.php';
?> 