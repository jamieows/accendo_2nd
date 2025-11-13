<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

// Load preferences
$theme = $_SESSION['theme'] ?? 'light';
$zoom  = $_SESSION['zoom']  ?? 1.0;

$darkClass = $theme === 'dark' ? 'dark-mode' : '';
?>
<!DOCTYPE html>
<html lang="en" class="<?= $darkClass ?>" style="font-size: calc(16px * <?= $zoom ?>);">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accendo – <?= $pageTitle ?? 'Student Portal' ?></title>

    <link rel="stylesheet" href="css/student.css">

    <!-- CSS Variables (Light + Dark) -->
    <style>
        :root {
            --sidebar-width: 220px;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --accent: #2563eb;
            --radius: 12px;
        }
        .dark-mode {
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --border: #334155;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.3);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.4);
            --accent: #60a5fa;
        }
        
        body { 
            background: var(--bg); 
            color: var(--text);
            margin: 0;
            padding: 0;
        }
        
        /* Main content layout - adjusts based on sidebar */
        .content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        
        /* When sidebar is hidden, content expands to full width */
        body.sidebar-hidden .content {
            margin-left: 0;
        }
        
        .card, .material-card, .assignment-card { 
            background: var(--card); 
            border-color: var(--border); 
            box-shadow: var(--shadow-sm); 
        }
        .card-title, .material-title, .assignment-title { color: var(--text); }
        .subject-desc, .stat-label, .no-materials { color: var(--text-muted); }
        .btn-open, .btn-success { background: #10b981; }
        .btn-open:hover { background: #059669; }
        .voice-btn { border: 1.5px solid var(--accent); color: var(--accent); }
        .voice-btn:hover { background: var(--accent); color: white; }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>

    <style>
        :root { --zoom-level: <?= $zoom ?>; }
        html { transition: font-size 0.2s ease; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="content">