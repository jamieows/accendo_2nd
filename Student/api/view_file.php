<?php
// api/view_file.php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    exit('Unauthorized');
}

// ----------------------------------------------------------
// 1. Validate request
// ----------------------------------------------------------
$materialId = $_GET['id'] ?? '';
if (!ctype_digit($materialId)) {
    http_response_code(400);
    exit('Invalid material ID');
}
$materialId = (int)$materialId;

// ----------------------------------------------------------
// 2. Verify ownership (same check you already use)
// ----------------------------------------------------------
$stmt = $pdo->prepare("
    SELECT m.file_path
    FROM materials m
    JOIN subjects s ON m.subject_id = s.id
    JOIN student_subjects ss ON ss.subject_id = s.id
    WHERE m.id = ? AND ss.student_id = ?
");
$stmt->execute([$materialId, $_SESSION['user_id']]);
$material = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$material) {
    http_response_code(403);
    exit('Access denied');
}

// ----------------------------------------------------------
// 3. Resolve safe absolute path
// ----------------------------------------------------------
$relativePath = $material['file_path'];               // e.g. uploads/subj1/file.pdf
$absolutePath = realpath('../../' . $relativePath);

if ($absolutePath === false || strpos($absolutePath, realpath('../../uploads')) !== 0) {
    http_response_code(400);
    exit('Invalid file path');
}
if (!file_exists($absolutePath)) {
    http_response_code(404);
    exit('File not found');
}

// ----------------------------------------------------------
// 4. MIME-type map (add more if needed)
// ----------------------------------------------------------
$ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
$mimeMap = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',  'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'mp4'  => 'video/mp4',
    'webm' => 'video/webm',
    'ogg'  => 'video/ogg',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt'  => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.document',
];
$contentType = $mimeMap[$ext] ?? 'application/octet-stream';

// ----------------------------------------------------------
// 5. Send *inline* headers + stream file
// ----------------------------------------------------------
header('Content-Type: ' . $contentType);
header('Content-Disposition: inline; filename="' . basename($absolutePath) . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($absolutePath));

$fp = fopen($absolutePath, 'rb');
while (!feof($fp)) {
    echo fread($fp, 8192);
    flush();
}
fclose($fp);
exit;