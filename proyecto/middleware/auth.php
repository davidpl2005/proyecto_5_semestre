<?php

function checkAuth() {
    if (!isset($_SESSION['user'])) {
        header('Location: /Proyecto_aula/proyecto/views/auth/login.php');
        exit;
    }
}

function checkAdmin() {
    checkAuth();
    if (!isset($_SESSION['user']['rol']) || $_SESSION['user']['rol'] !== 'admin') {
        header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
        exit;
    }
}

function checkChef() {
    checkAuth();
    if (!isset($_SESSION['user']['rol']) || $_SESSION['user']['rol'] !== 'chef') {
        header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
        exit;
    }
}

function checkAdminOrChef() {
    checkAuth();
    $rol = $_SESSION['user']['rol'] ?? '';
    if ($rol !== 'admin' && $rol !== 'chef') {
        header('Location: /Proyecto_aula/proyecto/views/menu/index.php');
        exit;
    }
}