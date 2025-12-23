<?php 
require_once 'db.php'; 
require_once 'dil.php'; 

$conn = new mysqli($hn, $un, $pw, $db);
$error = "";

if (isset($_POST['email'])) {
    $name = sanitize($conn, $_POST['name']);
    $surname = sanitize($conn, $_POST['surname']);
    $email = sanitize($conn, $_POST['email']);
    $sec_q = sanitize($conn, $_POST['security_question']);
    $sec_a = sanitize($conn, $_POST['security_answer']);
    $pass = $_POST['password'];
    $pass_confirm = $_POST['password_confirm'];
    
    if ($pass !== $pass_confirm) {
        $error = $l['pass_mismatch'];
    } elseif (strlen($pass) < 6 || !preg_match("#[0-9]+#", $pass) || !preg_match("#[a-zA-Z]+#", $pass)) {
        $error = $l['pass_weak_msg'];
    } else {
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $error = $l['email_not_found']; 
        } else {
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, surname, email, password, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $surname, $email, $hashed_pass, $sec_q, $sec_a);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['name'] = $name . " " . $surname;
                $_SESSION['is_admin'] = 0;
                $ret = isset($_GET['ret']) ? $_GET['ret'] : 'index.php';
                header("Location: $ret"); exit;
            } else { $error = "Veritabanı hatası!"; }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $l['register_title']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body id="mainBody" class="page-center">

<div class="fixed-btn-right">
    <button class="fixed-toggle" onclick="toggleDarkMode()" id="modeBtn">🌙</button>
    <a href="?lang=<?php echo $lang_code == 'tr' ? 'en' : 'tr'; ?>" class="fixed-toggle"><?php echo $lang_code == 'tr' ? 'EN' : 'TR'; ?></a>
</div>

<div class="card login-card" style="margin: 0; max-width: 450px;">
    <a href="index.php" class="login-brand" style="display:flex; flex-direction:column; align-items:center; gap:10px;">
        <img src="logo.png?v=<?php echo time(); ?>" alt="Logo" style="height: 60px; width: auto;">
        <span>Vote<span style="color:var(--accent-color)">Pool</span></span>
    </a>

    <h2 class="mb-20"><?php echo $l['register_title']; ?></h2>
    <?php if($error) echo "<div class='login-error'>$error</div>"; ?>
    
    <form method="post">
        <div class="d-flex gap-10 mb-15">
            <div class="flex-1 text-left"><label class="d-block mb-5"><?php echo $l['name']; ?></label><input type="text" name="name" required class="w-100" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>"></div>
            <div class="flex-1 text-left"><label class="d-block mb-5"><?php echo $l['surname']; ?></label><input type="text" name="surname" required class="w-100" value="<?php echo isset($_POST['surname']) ? $_POST['surname'] : ''; ?>"></div>
        </div>
        <div class="mb-15 text-left"><label class="d-block mb-5"><?php echo $l['email']; ?></label><input type="email" name="email" required class="w-100" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>"></div>
        <div class="mb-15 text-left">
            <label class="d-block mb-5"><?php echo $l['sec_q_label']; ?></label>
            <select name="security_question"><option value="first_pet"><?php echo $l['q_pet']; ?></option><option value="mother_maiden"><?php echo $l['q_mom']; ?></option><option value="favorite_color"><?php echo $l['q_color']; ?></option><option value="primary_school"><?php echo $l['q_school']; ?></option></select>
            <input type="text" name="security_answer" placeholder="<?php echo $l['sec_a_placeholder']; ?>" required class="w-100 mt-10">
        </div>
        <div class="d-flex gap-10 mb-20">
            <div class="flex-1 text-left"><label class="d-block mb-5"><?php echo $l['password']; ?></label><input type="password" name="password" required class="w-100"></div>
            <div class="flex-1 text-left"><label class="d-block mb-5"><?php echo $l['pass_confirm']; ?></label><input type="password" name="password_confirm" required class="w-100"></div>
        </div>
        <div class="text-left mb-20" style="font-size:0.8rem; color:var(--text-secondary);"><?php echo $l['pass_rule']; ?></div>
        <button type="submit" class="btn btn-primary w-100 justify-center"><?php echo $l['register_submit']; ?></button>
    </form>
    <div class="mt-20" style="font-size:0.9rem;"><?php echo $l['have_account']; ?> <a href="giris.php" style="color:var(--accent-color); font-weight:600; text-decoration:none;"><?php echo $l['login_title']; ?></a></div>
</div>
<script>
function toggleDarkMode() { const body = document.body; const btn = document.getElementById('modeBtn'); body.classList.toggle('dark-mode'); if (body.classList.contains('dark-mode')) { localStorage.setItem('theme', 'dark'); if(btn) btn.innerHTML = "☀️"; } else { localStorage.setItem('theme', 'light'); if(btn) btn.innerHTML = "🌙"; } }
window.onload = function() { if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-mode'); const btn = document.getElementById('modeBtn'); if(btn) btn.innerHTML = "☀️"; } }
</script>
</body>
</html>