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