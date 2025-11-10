<?php
require_once '../../config/db.php';
if($_SESSION['role']!=='admin') die();
if(isset($_GET['action']) && $_GET['action']==='delete' && isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM users WHERE id=? AND role!='admin'")->execute([$id]);
}
header("Location: ../manage_users.php");
?>