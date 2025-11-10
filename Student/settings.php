<?php 
require_once '../config/db.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
?>
<?php include 'includes/header.php'; ?>

<h1>Settings</h1>
<div class="card">
  <h2>Voice Assistant</h2>
  <label for="nlp-volume">Volume: <span id="vol-value">70</span>%</label>
  <input type="range" id="nlp-volume" min="0" max="100" value="70" style="width:100%;">

  <button onclick="speak('Testing voice assistant. Volume is now at ' + document.getElementById('nlp-volume').value + ' percent.')" 
          class="btn" style="margin-top:15px;">Test Voice</button>
</div>

<?php include 'includes/footer.php'; ?>