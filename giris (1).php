<?php 
require_once 'db.php'; 
require_once 'dil.php'; 

$conn = new mysqli($hn, $un, $pw, $db);
$error = "";

if (isset($_POST['email'])) {
    $email = sanitize($conn, $_POST['email']);
    $pass = $_POST['password']; 
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'] . " " . $row['surname'];
            $_SESSION['is_admin'] = $row['is_admin'];
            
            $ret = isset($_GET['ret']) ? $_GET['ret'] : 'index.php';
            header("Location: $ret");
            exit;
        } else {
            $error = "Hatalı şifre!";
        }
    } else {
        $error = "Bu e-posta ile kayıtlı kullanıcı bulunamadı!";
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $l['login_title']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body id="mainBody" class="page-center">

<div class="fixed-btn-right">
    <button class="fixed-toggle" onclick="toggleDarkMode()" id="modeBtn">🌙</button>
    <a href="?lang=<?php echo $lang_code == 'tr' ? 'en' : 'tr'; ?>" class="fixed-toggle"><?php echo $lang_code == 'tr' ? 'EN' : 'TR'; ?></a>
</div>

<div class="card login-card" style="margin: 0;">
    <a href="index.php" class="login-brand" style="display:flex; flex-direction:column; align-items:center; gap:10px;">
        <img src="logo.png?v=<?php echo time(); ?>" alt="Logo" style="height: 60px; width: auto;">
        <span>Vote<span style="color:var(--accent-color)">Pool</span></span>
    </a>
    
    <h2 class="mb-20"><?php echo $l['login_title']; ?></h2>
    
    <?php if($error) echo "<div class='login-error'>$error</div>"; ?>
    
    <form method="post">
        <div class="mb-15 text-left">
            <label class="d-block mb-5"><?php echo $l['email']; ?></label>
            <input type="email" name="email" required class="w-100">
        </div>
        <div class="mb-20 text-left">
            <label class="d-block mb-5"><?php echo $l['password']; ?></label>
            <input type="password" name="password" required class="w-100">
            <div class="text-right mt-10">
                <a href="sifremi-unuttum.php" style="font-size:0.85rem; color:var(--accent-color); text-decoration:none; font-weight:500;"><?php echo $l['forgot_pass_link']; ?></a>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 justify-center"><?php echo $l['login_btn']; ?></button>
    </form>
    
    <div class="mt-20" style="font-size:0.9rem;">
        <?php echo $l['no_account']; ?> <a href="kayit.php" style="color:var(--accent-color); font-weight:600; text-decoration:none;"><?php echo $l['register_title']; ?></a>
    </div>
</div>

<script>
function toggleDarkMode() { const body = document.body; const btn = document.getElementById('modeBtn'); body.classList.toggle('dark-mode'); if (body.classList.contains('dark-mode')) { localStorage.setItem('theme', 'dark'); if(btn) btn.innerHTML = "☀️"; } else { localStorage.setItem('theme', 'light'); if(btn) btn.innerHTML = "🌙"; } }
window.onload = function() { if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-mode'); const btn = document.getElementById('modeBtn'); if(btn) btn.innerHTML = "☀️"; } }
</script>
</body>
</html>