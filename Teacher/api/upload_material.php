<?php
require_once '../../config/db.php';
if($_SESSION['role']!=='teacher') die();

if($_FILES['file']['error']===0){
    $ext = pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
    $type = in_array($ext,['pdf'])?'pdf':(in_array($ext,['doc','docx'])?'doc':'video');
    $path = "../../uploads/".uniqid().".$ext";
    move_uploaded_file($_FILES['file']['tmp_name'],$path);
    $stmt = $pdo->prepare("INSERT INTO materials (teacher_id,subject_id,title,file_path,file_type) VALUES (?,?,?,?,?)");
    $stmt->execute([$_SESSION['user_id'],$_POST['subject_id'],$_POST['title'],$path,$type]);
}
header("Location: ../my_courses.php");
?>