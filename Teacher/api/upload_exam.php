<?php
require_once '../../config/db.php';
if($_SESSION['role']!=='teacher') die();

if($_FILES['file']['error']===0){
    $ext = pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
    $path = "../../uploads/".uniqid().".$ext";
    move_uploaded_file($_FILES['file']['tmp_name'],$path);
    $stmt = $pdo->prepare("INSERT INTO exams (teacher_id,subject_id,title,file_path,start_time,end_time) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$_SESSION['user_id'],$_POST['subject_id'],$_POST['title'],$path,$_POST['start_time'],$_POST['end_time']]);
}
header("Location: ../exams.php");
?>