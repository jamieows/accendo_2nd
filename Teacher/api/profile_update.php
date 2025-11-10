<?php
require_once '../../config/db.php';
if($_SESSION['role']!=='teacher') die();

$uid = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
$stmt->execute([$uid]);
$hash = $stmt->fetchColumn();

$update = "UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?";
$params = [$_POST['first_name'], $_POST['last_name'], $_POST['email'], $uid];

if(!empty($_POST['old_password'])){
    if(password_verify($_POST['old_password'],$hash) && $_POST['new_password']===$_POST['confirm_password'] && strlen($_POST['new_password'])>=6){
        $update = "UPDATE users SET first_name=?, last_name=?, email=?, password=? WHERE id=?";
        $params = [$_POST['first_name'], $_POST['last_name'], $_POST['email'], password_hash($_POST['new_password'],PASSWORD_DEFAULT), $uid];
    } else {
        die("Password mismatch or too short.");
    }
}
$pdo->prepare($update)->execute($params);
header("Location: ../profile.php");
?>