<?php
require_once '../../config/db.php';
if($_SESSION['role']!=='teacher') die();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $stmt = $pdo->prepare("INSERT INTO assignments (teacher_id,subject_id,title,description,due_date) VALUES (?,?,?,?,?)");
    $stmt->execute([$_SESSION['user_id'],$_POST['subject_id'],$_POST['title'],$_POST['description'],$_POST['due_date']]);
}
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM assignments WHERE id=? AND teacher_id=?")->execute([$id,$_SESSION['user_id']]);
}
header("Location: ../assignments.php");
?>