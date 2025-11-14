<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

// Load theme preference
$theme = $_SESSION['theme'] ?? 'light';
$darkClass = $theme === 'dark' ? 'dark-mode' : '';
?>
<!DOCTYPE html>
<html lang="en" class="<?= $darkClass ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accendo – <?= $pageTitle ?? 'Student Portal' ?></title>

    <!-- Professional Font System -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- CSS Variables (Light + Dark) -->
    <style>
        :root {
            /* Font System - 3 Professional Fonts */
            --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            --font-heading: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            --font-mono: 'JetBrains Mono', 'Courier New', monospace;
            
            /* Layout */
            --sidebar-width: 220px;
            
            /* Light Mode Colors */
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
            /* Dark Mode Colors */
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --border: #334155;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.3);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.4);
            --accent: #60a5fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            background: var(--bg); 
            color: var(--text);
            font-family: var(--font-primary);
            line-height: 1.6;
            transition: background-color 0.3s ease, color 0.3s ease;
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
        
        /* Smooth fade-in animation for page load */
        .content > * {
            animation: fadeInUp 0.5s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card, .material-card, .assignment-card { 
            background: var(--card); 
            border: 1px solid var(--border); 
            box-shadow: var(--shadow-sm);
            border-radius: var(--radius);
            transition: all 0.3s ease;
        }
        
        .card:hover, .material-card:hover, .assignment-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        .card-title, .material-title, .assignment-title { 
            color: var(--text);
            font-family: var(--font-heading);
        }
        
        .subject-desc, .stat-label, .no-materials { 
            color: var(--text-muted); 
        }
        
        .btn-open, .btn-success { 
            background: #10b981; 
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-family: var(--font-primary);
            transition: all 0.2s ease;
        }
        
        .btn-open:hover, .btn-success:hover { 
            background: #059669; 
            transform: translateY(-1px);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body class="<?= $darkClass ?>">
    <?php include 'sidebar.php'; ?>
    <main class="content">