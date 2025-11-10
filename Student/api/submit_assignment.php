<?php
require_once '../../config/db.php';
if($_SESSION['role']!=='student') die();

if($_FILES['file']['error']===0){
    $path = "../../uploads/sub_".uniqid().".".pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
    move_uploaded_file($_FILES['file']['tmp_name'],$path);
    $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id,student_id,file_path) VALUES (?,?,?)");
    $stmt->execute([$_POST['assignment_id'],$_SESSION['user_id'],$path]);
}
header("Location: ../assignments.php");
?>