<?php 
require_once 'db.php'; 
require_once 'dil.php';

$conn = new mysqli($hn, $un, $pw, $db);

if (!isset($_SESSION['user_id'])) { header("Location: giris.php"); exit; }
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 0;

if (isset($_GET['action']) && $_GET['action'] == 'logout') { session_destroy(); header("Location: index.php"); exit; }

if (isset($_POST['delete_own_poll'])) {
    $pid = $conn->real_escape_string($_POST['poll_id']);
    $check = $conn->query("SELECT * FROM polls WHERE id='$pid' AND created_by='$user_id'");
    if ($check->num_rows > 0) {
        $imgs = $conn->query("SELECT image_path FROM options WHERE poll_id='$pid'");
        while($r = $imgs->fetch_assoc()){ if($r['image_path'] && file_exists($r['image_path'])) unlink($r['image_path']); }
        $conn->query("DELETE FROM votes_history WHERE poll_id='$pid'");
        $conn->query("DELETE FROM options WHERE poll_id='$pid'");
        $conn->query("DELETE FROM polls WHERE id='$pid'");
        header("Location: profil.php");
    }
}

// İstatistikleri Çek
$stat_votes = $conn->query("SELECT COUNT(DISTINCT poll_id) FROM votes_history WHERE user_id='$user_id'")->fetch_array()[0];
$stat_polls = $conn->query("SELECT COUNT(*) FROM polls WHERE created_by='$user_id'")->fetch_array()[0];

$badges = [];

// --- GÜNCELLENMİŞ ROZET TANIMLARI ---
// Renkler ve İkonlar sayının büyüklüğüne göre değişiyor (Bronz -> Gümüş -> Altın -> Elmas -> Mor)
$vote_milestones = [
    1   => ['icon' => '🗳️', 'color' => '#E0F2F1', 'text' => '#00695C', 'key' => 'badge_v1'],   // Başlangıç Yeşili
    5   => ['icon' => '🥉', 'color' => '#FFF3E0', 'text' => '#E65100', 'key' => 'badge_v5'],   // Bronz
    10  => ['icon' => '🥈', 'color' => '#ECEFF1', 'text' => '#455A64', 'key' => 'badge_v10'],  // Gümüş
    25  => ['icon' => '🥇', 'color' => '#FFF8E1', 'text' => '#F57F17', 'key' => 'badge_v25'],  // Altın
    50  => ['icon' => '💎', 'color' => '#E3F2FD', 'text' => '#1565C0', 'key' => 'badge_v50'],  // Elmas Mavisi
    100 => ['icon' => '👑', 'color' => '#F3E5F5', 'text' => '#6A1B9A', 'key' => 'badge_v100'] // Kraliyet Moru
];

$create_milestones = [
    1   => ['icon' => '💡', 'color' => '#F1F8E9', 'text' => '#33691E', 'key' => 'badge_c1'],   // Fikir Yeşili
    5   => ['icon' => '📢', 'color' => '#E8F5E9', 'text' => '#1B5E20', 'key' => 'badge_c5'],   // Ses Yeşili
    10  => ['icon' => '🚀', 'color' => '#E1F5FE', 'text' => '#01579B', 'key' => 'badge_c10'],  // Yükseliş Mavisi
    25  => ['icon' => '🔥', 'color' => '#FFEBEE', 'text' => '#B71C1C', 'key' => 'badge_c25'],  // Ateş Kırmızısı
    50  => ['icon' => '🌟', 'color' => '#FCE4EC', 'text' => '#880E4F', 'key' => 'badge_c50'],  // Yıldız Pembesi
    100 => ['icon' => '🪐', 'color' => '#E0F7FA', 'text' => '#006064', 'key' => 'badge_c100'] // Evren Turkuazı
];

// Kazanılan rozetleri hesapla (Sadece en yüksek seviyeyi değil, kazanılan hepsini gösterelim)
foreach ($vote_milestones as $limit => $data) { 
    if ($stat_votes >= $limit) $badges[] = $data; 
}
foreach ($create_milestones as $limit => $data) { 
    if ($stat_polls >= $limit) $badges[] = $data; 
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <title>Profil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body id="mainBody">

<div class="fixed-btn-right">
    <button class="fixed-toggle" onclick="toggleDarkMode()" id="modeBtn">🌙</button>
    <a href="?lang=<?php echo $lang_code == 'tr' ? 'en' : 'tr'; ?>" class="fixed-toggle"><?php echo $lang_code == 'tr' ? 'EN' : 'TR'; ?></a>
</div>

<div class="container">
    <nav class="navbar">
        <a href="index.php" class="brand">
            <img src="logo.png?v=<?php echo time(); ?>" alt="Logo" class="logo-img" onerror="this.style.display='none'">
            Vote<span style="color:var(--accent-color)">Pool</span>
        </a>
        <div class="nav-links">
            <?php if($is_admin): ?>
                <a href="admin.php" class="btn btn-secondary"><?php echo $l['admin_panel']; ?></a>
            <?php endif; ?>
            <a href="profil.php" class="btn-profile">👤 <?php echo $_SESSION['name']; ?></a>
            <a href="?action=logout" class="btn btn-danger"><?php echo $l['logout']; ?></a>
        </div>
    </nav>

    <div class="card">
        <h3 class="section-title">🏆 <?php echo $l['stats']; ?></h3>
        
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-num"><?php echo $stat_votes; ?></div>
                <div class="stat-label"><?php echo $l['total_votes_cast']; ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-num"><?php echo $stat_polls; ?></div>
                <div class="stat-label"><?php echo $l['total_polls_created']; ?></div>
            </div>
        </div>

        <?php if (!empty($badges)): ?>
            <div style="font-weight:600; margin-bottom:15px; margin-top:20px; border-top:1px solid var(--border-color); padding-top:20px;">
                <?php echo $l['my_badges']; ?>:
            </div>
            <div class="badge-container">
                <?php foreach($badges as $b): ?>
                    <div class="badge-pill" style="background:<?php echo $b['color']; ?>; color:<?php echo $b['text']; ?>; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <span style="font-size:1.2rem;"><?php echo $b['icon']; ?></span> 
                        <?php echo isset($l[$b['key']]) ? $l[$b['key']] : $b['key']; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color:var(--text-secondary); font-size:0.9rem; margin-top:15px;">
                Henüz rozet kazanmadınız. Oy vererek veya anket oluşturarak rozet kazanmaya başlayın! 🚀
            </p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3 class="section-title">📂 <?php echo $l['created_polls']; ?></h3>
        <ul class="list-group">
            <?php
            $res = $conn->query("SELECT * FROM polls WHERE created_by='$user_id' ORDER BY id DESC");
            if ($res->num_rows > 0):
                while ($row = $res->fetch_assoc()):
            ?>
                <li class="list-item-profile">
                    <div>
                        <strong><?php echo $row['question']; ?></strong>
                        <div style="font-size:0.85rem; color:var(--text-secondary); margin-top:5px;">
                            <?php echo $row['is_anonymous'] ? '🕵️ '.$l['make_anon'] : '👤 '.$row['creator_name']; ?> | 
                            <?php echo $row['quota']; ?> <?php echo $l['quota']; ?>
                        </div>
                    </div>
                    <form method="post" onsubmit="return confirm('<?php echo $l['del_confirm_poll']; ?>')">
                        <input type="hidden" name="poll_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete_own_poll" class="btn btn-danger" style="padding:6px 12px; font-size:0.8rem;"><?php echo $l['delete']; ?></button>
                    </form>
                </li>
            <?php endwhile; else: ?>
                <div class="text-center" style="padding:20px; color:var(--text-secondary);"><?php echo $l['no_record']; ?></div>
            <?php endif; ?>
        </ul>
    </div>

    <div class="card">
        <h3 class="section-title">🗳️ <?php echo $l['voted_polls']; ?></h3>
        <ul class="list-group">
            <?php
            $sql = "SELECT DISTINCT p.question, v.vote_time FROM votes_history v JOIN polls p ON v.poll_id = p.id WHERE v.user_id='$user_id' ORDER BY v.vote_time DESC";
            $res = $conn->query($sql);
            if ($res->num_rows > 0):
                while ($row = $res->fetch_assoc()):
            ?>
                <li class="list-item-profile">
                    <span><?php echo $row['question']; ?></span>
                    <span style="font-size:0.85rem; color:var(--text-secondary);"><?php echo date("d.m.Y", strtotime($row['vote_time'])); ?></span>
                </li>
            <?php endwhile; else: ?>
                <div class="text-center" style="padding:20px; color:var(--text-secondary);"><?php echo $l['no_record']; ?></div>
            <?php endif; ?>
        </ul>
    </div>

</div>

<script>
    function toggleDarkMode() {
        const body = document.body;
        const btn = document.getElementById('modeBtn');
        body.classList.toggle('dark-mode');
        if (body.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark');
            if(btn) btn.innerHTML = "☀️";
        } else {
            localStorage.setItem('theme', 'light');
            if(btn) btn.innerHTML = "🌙";
        }
    }
    
    window.onload = function() {
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
            const btn = document.getElementById('modeBtn');
            if(btn) btn.innerHTML = "☀️";
        }
    }
</script>
</body>
</html>