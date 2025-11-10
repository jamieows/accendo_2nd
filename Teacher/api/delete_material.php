<?php
require_once '../../config/db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    die('Unauthorized');
}

if (isset($_POST['material_id'])) {
    $materialId = $_POST['material_id'];
    
    // Verify the material belongs to this teacher
    $stmt = $pdo->prepare("SELECT file_path FROM materials WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$materialId, $_SESSION['user_id']]);
    $material = $stmt->fetch();
    
    if ($material) {
        // Delete file from disk if it exists
        $filePath = '../../uploads/' . $material['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete from database
        $deleteStmt = $pdo->prepare("DELETE FROM materials WHERE id = ? AND teacher_id = ?");
        $deleteStmt->execute([$materialId, $_SESSION['user_id']]);
    }
}

header('Location: ../my_courses.php');
exit();
?>
