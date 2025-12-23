<?php 
require_once 'db.php'; 
require_once 'dil.php'; 

$conn = new mysqli($hn, $un, $pw, $db);
$error = "";
$success = "";
$step = 1; 

// Soruların çevirisi
$questions_map = [
    'first_pet' => $l['q_pet'],
    'mother_maiden' => $l['q_mom'],
    'favorite_color' => $l['q_color'],
    'primary_school' => $l['q_school']
];

if (isset($_POST['check_email'])) {
    $email = sanitize($conn, $_POST['email']);
    $check = $conn->query("SELECT id, security_question FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $user = $check->fetch_assoc();
        $_SESSION['reset_id'] = $user['id'];
        $_SESSION['reset_q'] = $user['security_question'];
        $step = 2;
    } else {
        $error = $l['email_not_found'];
    }
} elseif (isset($_POST['check_answer'])) {
    $ans = sanitize($conn, $_POST['answer']);
    $uid = $_SESSION['reset_id'];
    $check = $conn->query("SELECT security_answer FROM users WHERE id='$uid'");
    $user = $check->fetch_assoc();
    
    if (mb_strtolower($user['security_answer']) == mb_strtolower($ans)) {
        $step = 3;
        $_SESSION['reset_verified'] = true;
    } else {
        $error = $l['sec_ans_wrong'];
        $step = 2;
    }
} elseif (isset($_POST['new_password_submit'])) {
    if (!isset($_SESSION['reset_verified'])) { header("Location: sifremi-unuttum.php"); exit; }
    
    $pass = $_POST['password'];
    $pass_conf = $_POST['password_confirm'];
    $uid = $_SESSION['reset_id'];
    
    if ($pass !== $pass_conf) {
        $error = $l['pass_mismatch'];
        $step = 3;
    } elseif (strlen($pass) < 6 || !preg_match("#[0-9]+#", $pass) || !preg_match("#[a-zA-Z]+#", $pass)) {
        $error = $l['pass_weak_msg'];
        $step = 3;
    } else {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hashed' WHERE id='$uid'");
        $success = $l['pass_reset_success'];
        session_destroy(); 
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $l['forgot_pass_title']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body id="mainBody" class="page-center">

<div class="fixed-btn-right">
    <button class="fixed-toggle" onclick="toggleDarkMode()" id="modeBtn">🌙</button>
    <a href="?lang=<?php echo $lang_code == 'tr' ? 'en' : 'tr'; ?>" class="fixed-toggle"><?php echo $lang_code == 'tr' ? 'EN' : 'TR'; ?></a>
</div>

<div class="card login-card" style="margin: 0;">
    <a href="index.php" class="login-brand">Vote<span style="color:var(--accent-color)">Pool</span></a>
    <h3 class="mb-20">🔑 <?php echo $l['forgot_pass_title']; ?></h3>
    
    <?php if($error) echo "<div class='login-error'>$error</div>"; ?>
    <?php if($success): ?>
        <div style="color:var(--accent-text); background:var(--accent-color); padding:10px; border-radius:8px; margin-bottom:15px;"><?php echo $success; ?></div>
        <a href="giris.php" class="btn btn-primary w-100 justify-center"><?php echo $l['login_btn']; ?></a>
    <?php else: ?>

        <?php if($step == 1): ?>
        <form method="post">
            <div class="mb-20 text-left">
                <label class="d-block mb-5"><?php echo $l['email']; ?></label>
                <input type="email" name="email" required class="w-100">
            </div>
            <button type="submit" name="check_email" class="btn btn-primary w-100 justify-center"><?php echo $l['continue']; ?></button>
        </form>
        <?php endif; ?>

        <?php if($step == 2): ?>
        <form method="post">
            <div class="mb-20 text-left">
                <label class="d-block mb-5"><?php echo $l['sec_q_label']; ?>:</label>
                <div style="font-weight:700; margin-bottom:10px; font-size:1.1rem; color:var(--accent-color);">
                    <?php echo isset($questions_map[$_SESSION['reset_q']]) ? $questions_map[$_SESSION['reset_q']] : $_SESSION['reset_q']; ?>
                </div>
                <input type="text" name="answer" placeholder="<?php echo $l['sec_a_placeholder']; ?>" required autofocus class="w-100">
            </div>
            <button type="submit" name="check_answer" class="btn btn-primary w-100 justify-center"><?php echo $l['verify']; ?></button>
        </form>
        <?php endif; ?>

        <?php if($step == 3): ?>
        <form method="post">
            <div class="mb-15 text-left">
                <label class="d-block mb-5"><?php echo $l['new_pass']; ?></label>
                <input type="password" name="password" required class="w-100">
            </div>
            <div class="mb-20 text-left">
                <label class="d-block mb-5"><?php echo $l['pass_confirm']; ?></label>
                <input type="password" name="password_confirm" required class="w-100">
            </div>
            <button type="submit" name="new_password_submit" class="btn btn-primary w-100 justify-center"><?php echo $l['update_pass']; ?></button>
        </form>
        <?php endif; ?>

        <div class="mt-20">
            <a href="giris.php" style="color:var(--text-secondary); text-decoration:none;"><?php echo $l['back_login']; ?></a>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleDarkMode() { const body = document.body; const btn = document.getElementById('modeBtn'); body.classList.toggle('dark-mode'); if (body.classList.contains('dark-mode')) { localStorage.setItem('theme', 'dark'); if(btn) btn.innerHTML = "☀️"; } else { localStorage.setItem('theme', 'light'); if(btn) btn.innerHTML = "🌙"; } }
window.onload = function() { if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-mode'); const btn = document.getElementById('modeBtn'); if(btn) btn.innerHTML = "☀️"; } }
</script>
</body>
</html>