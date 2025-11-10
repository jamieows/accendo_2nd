<?php
require_once '../../config/db.php';
if($_SESSION['role']!=='admin') die();
if(isset($_GET['action']) && $_GET['action']==='delete' && isset($_GET['id'])){
    $id = (int)$_GET['id'];
    // fetch user info for logging
    $u = $pdo->prepare("SELECT first_name, last_name, username, role FROM users WHERE id = ?");
    $u->execute([$id]);
    $usr = $u->fetch();
    $pdo->prepare("DELETE FROM users WHERE id=? AND role!='admin'")->execute([$id]);
    if ($usr) {
        $actor = $_SESSION['name'] ?? 'System';
        $role = $usr['role'] ?? 'user';
        $name = trim(($usr['first_name'] ?? '') . ' ' . ($usr['last_name'] ?? '')) ?: ($usr['username'] ?? '');
        // insert into activity_logs if table exists
        $tbl = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'activity_logs'");
        $tbl->execute();
        if ($tbl->fetchColumn()) {
            $ins = $pdo->prepare("INSERT INTO activity_logs (actor_name, action, target, details) VALUES (?, ?, ?, ?)");
            $ins->execute([$actor, 'delete', $role, $name]);
        }
    }
}
header("Location: ../manage_users.php");
?>