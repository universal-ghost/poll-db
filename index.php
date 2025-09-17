<?php
// index.php

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'my_survey_db';

// اتصال به دیتابیس
$conn = new mysqli($host, $user, $pass, $dbName);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// گرفتن لیست نظرسنجی‌ها
$polls = [];
$res = $conn->query("SELECT id, question FROM polls ORDER BY id ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) $polls[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>صفحه اصلی نظرسنجی</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script type="module" src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.js"></script>
<style>
body { margin:0; padding:0; font-family:sans-serif; background:#f5f6fa; }
.sidebar { width: 220px; background: #2f3640; height: 100vh; position: fixed; color:white; display:flex; flex-direction:column; padding-top:20px; }
.sidebar h5 { text-align:center; margin-bottom:20px; display:flex; align-items:center; justify-content:center; }
.sidebar h5 ion-icon { margin-left:10px; font-size:26px; }
.sidebar .btn { width: 90%; margin:5px auto; display:flex; align-items:center; justify-content:flex-start; color:white; border:none; background:#353b48; padding:10px 15px; border-radius:8px; transition:0.3s; text-decoration:none; font-size:1rem; }
.sidebar .btn:hover { background:#40739e; }
.sidebar ion-icon { margin-left:10px; font-size:22px; }
.content { margin-right: 220px; padding:20px; }
.header { height:60px; background:#273c75; color:white; display:flex; align-items:center; padding:0 20px; border-radius:8px; margin-bottom:20px; }
.header a { color:white; text-decoration:none; font-weight:bold; font-size:1.2rem; }

/* کارت‌های بزرگ‌تر و حرفه‌ای */
.poll-card {
    background: linear-gradient(145deg, #ffffff, #f0f0f5);
    border-radius: 18px;
    padding: 25px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s, background 0.3s;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 190px; /* ارتفاع بیشتر */
}
.poll-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 28px rgba(0,0,0,0.15);
    background: linear-gradient(145deg, #f5f6fa, #e6e9f0);
} 
.poll-card-header h5 {
    font-size: 1.3rem; /* متن بزرگ‌تر */
    font-weight: 600;
    color: #2f3640;
    line-height: 1.5;
}
.poll-card-header ion-icon {
    font-size: 1.6rem; /* آیکون بزرگ‌تر */
    color: #40739e;
    vertical-align: middle;
    margin-left: 8px;
}
.poll-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 18px;
    font-size: 1.05rem;
    color: #40739e;
}
.poll-card-footer ion-icon {
    vertical-align: middle;
    margin-right: 8px;
    font-size: 1.5rem; /* آیکون بزرگ‌تر */
}
@media (max-width: 768px) {
    .content { margin-right: 0; }
    .sidebar { position: relative; width: 100%; height: auto; flex-direction: row; justify-content: center; }
}
</style>
</head>
<body>

<div class="sidebar">
    <h5><ion-icon name="home-outline"></ion-icon> داشبورد</h5>
    <a href="setup.php" class="btn">
        <ion-icon name="settings-outline"></ion-icon> راه‌اندازی/تنظیم
    </a>
    <a href="create_poll.php" class="btn btn-primary">
        <ion-icon name="add-circle-outline"></ion-icon> ایجاد نظرسنجی جدید
    </a>
    <a href="results.php" class="btn">
        <ion-icon name="analytics-outline"></ion-icon> تحلیل نتایج
    </a>
</div>

<div class="content">
    <div class="header">
        <a href="setup.php">نظرسنجی‌ها</a>
    </div>

    <div class="container">
        <?php if(empty($polls)): ?>
            <div class="alert alert-info">هیچ نظرسنجی‌ای ایجاد نشده است. لطفاً به داشبورد مراجعه کنید.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach($polls as $poll): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="poll-card" onclick="window.location='poll.php?id=<?= $poll['id'] ?>'">
                            <div class="poll-card-header">
                                <h5><ion-icon name="grid-outline"></ion-icon> <?= htmlspecialchars($poll['question']) ?></h5>
                            </div>
                            <div class="poll-card-footer">
                                <span><ion-icon name="eye-outline"></ion-icon> مشاهده</span>
                                <span><ion-icon name="chevron-forward-outline"></ion-icon></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>

</html>
