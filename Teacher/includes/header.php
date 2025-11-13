<!DOCTYPE html>
<html lang="en">
<head>

<style>
  :root {
    --primary: #7B61FF;
    --text: #1f2937;
    --bg: #f9fafb;
    --card-bg: #ffffff;
    --border: #e5e7eb;
  }
  .dark-mode {
    --text: #f3f4f6;
    --bg: #0f172a;
    --card-bg: #1e293b;
    --border: #334155;
  }
  body {
    background: var(--bg);
    color: var(--text);
    transition: all 0.3s ease;
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  .btn {
    background: var(--card-bg);
    border: 1px solid var(--border);
    color: var(--text);
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.2s ease;
    padding: 0.5rem 1rem;
  }
  .btn:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-1px);
  }
</style>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher | Accendo LMS</title>
  <link rel="stylesheet" href="../assets/css/global.css">
  <link rel="stylesheet" href="../assets/css/teacher.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="../assets/js/global.js" defer></script>
  <script src="../assets/js/teacher.js" defer></script>
</head>
<body>
  <?php include 'sidebar.php'; ?>
  <div class="content">
    