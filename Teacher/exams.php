<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<style>
    /* === SPACING ONLY === */
    .exam-title {
        margin-bottom: 2.5rem; /* ← space between title and first field */
    }

    .exam-form label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text, #162447);
        text-transform: capitalize;
        letter-spacing: 0.3px;
    }

    .exam-form input,
    .exam-form select {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border: 1.5px solid #cccccc;
        border-radius: 0.6rem;
        background: #ffffff;
        color: #1a1a1a;
        transition: all 0.25s ease;
        box-sizing: border-box;
        margin-bottom: 1.75rem; /* ← space between each field */
    }

    .exam-form input:focus,
    .exam-form select:focus {
        outline: none;
        border-color: #7B61FF;
        box-shadow: 0 0 0 3px rgba(123,97,255,0.15);
    }

    .dark-mode .exam-form input,
    .dark-mode .exam-form select {
        background: #2a2a3a;
        color: #f0f0f0;
        border-color: #444454;
    }

    .dark-mode .exam-form input:focus,
    .dark-mode .exam-form select:focus {
        box-shadow: 0 0 0 3px rgba(167,139,250,0.25);
    }

    .exam-form .btn {
        background: #7B61FF;
        color: white;
        padding: 0.85rem 1.75rem;
        font-weight: 600;
        border: none;
        border-radius: 0.6rem;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.25s ease;
        width: 100%;
        margin-top: 1.5rem;
    }

    .exam-form .btn:hover {
        background: #6a51e6;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(123,97,255,0.3);
    }
</style>

<!-- Title with spacing below -->
<h1 class="exam-title">Upload Exam / Quiz</h1>

<!-- Form with spacing between fields -->
<form action="api/upload_exam.php" method="POST" enctype="multipart/form-data" class="exam-form">

  <label for="subject_id">Subject</label>
  <select name="subject_id" id="subject_id" required>
    <?php
    $stmt = $pdo->prepare("SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON ts.subject_id = s.id WHERE ts.teacher_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    while($s = $stmt->fetch()) echo "<option value='{$s['id']}'>{$s['name']}</option>";
    ?>
  </select>

  <label for="title">Title</label>
  <input type="text" id="title" name="title" required>

  <label for="file">File</label>
  <input type="file" id="file" name="file" accept=".pdf,.doc,.docx,.mp4" required>

  <label for="start_time">Start Time</label>
  <input type="datetime-local" id="start_time" name="start_time" required>

  <label for="end_time">End Time</label>
  <input type="datetime-local" id="end_time" name="end_time" required>

  <button type="submit" class="btn">Upload Exam</button>
</form>

<?php include 'includes/footer.php'; ?>
<script src="../assets/js/global.js"></script>
</body>
</html>