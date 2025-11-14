<?php
// student/api/list_folder.php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$parentId = $_GET['parent_id'] ?? 0;
if (!ctype_digit($parentId)) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT m.id, m.title, m.file_path, m.is_folder
    FROM materials m
    JOIN student_subjects ss ON ss.subject_id = m.subject_id
    WHERE m.parent_id = ? AND ss.student_id = ?
    ORDER BY m.is_folder DESC, m.title
");
$stmt->execute([$parentId, $_SESSION['user_id']]);
$items = $stmt->fetchAll();

$html = '';
foreach ($items as $item) {
    $icon = $item['is_folder'] ? 
        '<svg viewBox="0 0 24 24"><path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>' :
        '<svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6z"/></svg>';
    
    $html .= "<div class='folder-item' onclick='openMaterial({$item['id']})'>
        $icon <span>" . htmlspecialchars($item['title']) . "</span>
    </div>";
}

echo json_encode(['success' => true, 'html' => $html ?: '<p style="color:#888; text-align:center;">Empty folder</p>']);