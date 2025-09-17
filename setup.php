<?php
// setup.php

// تنظیمات اتصال دیتابیس
$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'my_survey_db';

// پیام‌ها
$messages = [];

// تابع اتصال به دیتابیس
function getConnection($withDb = false) {
    global $host, $user, $pass, $dbName;
    $conn = new mysqli($host, $user, $pass);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($withDb) {
        if (!$conn->select_db($dbName)) {
            die("Selecting DB failed: " . $conn->error);
        }
    }
    return $conn;
}

// پردازش فرم‌ها
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'create_db') {
            $conn = getConnection(false);
            $sql = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            if ($conn->query($sql)) {
                $messages[] = "✅ دیتابیس '$dbName' با موفقیت ایجاد یا قبلاً وجود داشته.";
            } else {
                $messages[] = "❌ خطا در ایجاد دیتابیس: " . $conn->error;
            }
            $conn->close();
        }

        if ($action === 'create_tables') {
            $conn = getConnection(true);
            $sql1 = "CREATE TABLE IF NOT EXISTS `polls` (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `question` VARCHAR(255) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB";
            $sql2 = "CREATE TABLE IF NOT EXISTS `options` (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `poll_id` INT NOT NULL,
                `option_text` VARCHAR(255) NOT NULL,
                `votes` INT DEFAULT 0,
                FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
            ) ENGINE=InnoDB";
            if ($conn->query($sql1) && $conn->query($sql2)) {
                $messages[] = "✅ جداول `polls` و `options` با موفقیت ایجاد شدند یا قبلاً وجود داشتند.";
            } else {
                $messages[] = "❌ خطا در ایجاد جداول: " . $conn->error;
            }
            $conn->close();
        }

        if ($action === 'clear_data') {
            $conn = getConnection(true);
            // غیرفعال کردن موقت محدودیت کلید خارجی
            $conn->query("SET FOREIGN_KEY_CHECKS = 0");
            $sql1 = "TRUNCATE TABLE `options`";
            $sql2 = "TRUNCATE TABLE `polls`";
            if ($conn->query($sql1) && $conn->query($sql2)) {
                $messages[] = "✅ اطلاعات جداول با موفقیت پاک شدند.";
            } else {
                $messages[] = "❌ خطا در پاک کردن اطلاعات: " . $conn->error;
            }
            // فعال کردن دوباره محدودیت‌ها
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            $conn->close();
        }

        if ($action === 'refresh') {
            $messages[] = "صفحه دوباره بارگذاری شد.";
        }
    }
}
?>

<!-- ادامه HTML -->
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تنظیم نظرسنجی</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script type="module" src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.js"></script>
<style>
body { margin:0; padding:0; font-family:sans-serif; background:#f5f6fa; }
.sidebar { width: 220px; background: #2f3640; height: 100vh; position: fixed; color:white; display:flex; flex-direction:column; padding-top:20px; }
.sidebar h5 { text-align:center; margin-bottom:20px; display:flex; align-items:center; justify-content:center; }
.sidebar h5 ion-icon { margin-left:10px; font-size:24px; }
.sidebar .btn { width: 90%; margin:5px auto; display:flex; align-items:center; justify-content:flex-start; color:white; border:none; background:#353b48; padding:10px 15px; border-radius:8px; transition:0.3s; }
.sidebar .btn:hover { background:#40739e; }
.sidebar ion-icon { margin-left:10px; font-size:20px; }
.content { margin-right: 220px; padding:20px; }
.header { height:60px; background:#273c75; color:white; display:flex; align-items:center; padding:0 20px; border-radius:8px; margin-bottom:20px; }
.header a { color:white; text-decoration:none; font-weight:bold; font-size:1.2rem; }
.card { margin-bottom:20px; border-radius:8px; }
.msg { margin:10px 0; font-weight:bold; }
.msg-success { color:green; }
.msg-error { color:red; }
table { width:100%; border-collapse:collapse; }
th, td { padding:10px; text-align:right; border-bottom:1px solid #dcdde1; }
th { width:180px; background:#f1f2f6; }
</style>
</head>
<body>

<div class="sidebar">
<h5><ion-icon name="settings-outline"></ion-icon> کنترل پنل</h5>
<form method="post">
        <a href="index.php" class="btn">
        <ion-icon name="home-outline"></ion-icon> صفحه اصلی
    </a>
    <button name="action" value="create_db" class="btn">
        <ion-icon name="cloud-outline"></ion-icon> ایجاد دیتابیس
    </button>
    <button name="action" value="create_tables" class="btn">
        <ion-icon name="server-outline"></ion-icon> ایجاد جداول
    </button>
    <button name="action" value="clear_data" class="btn">
        <ion-icon name="trash-outline"></ion-icon> پاک کردن
    </button>
    <button name="action" value="refresh" class="btn">
        <ion-icon name="refresh-outline"></ion-icon> رفرش صفحه
    </button>
</form>
</div>

<div class="content">
<div class="header">
    <b>راه‌اندازی نظرسنجی</b>
</div>

<?php foreach ($messages as $msg): ?>
    <?php $cls = strpos($msg, '✅') === 0 ? 'msg-success' : 'msg-error'; ?>
    <div class="msg <?= $cls ?>"><?= htmlspecialchars($msg) ?></div>
<?php endforeach; ?>

<div class="card p-3 shadow-sm">
<h5>اطلاعات اتصال به پایگاه داده:</h5>
<table>
<tr><th>نام سرور</th><td><?= htmlspecialchars($host) ?></td></tr>
<tr><th>نام پایگاه داده</th><td><?= htmlspecialchars($dbName) ?></td></tr>
<tr><th>نام کاربری</th><td><?= htmlspecialchars($user) ?></td></tr>
<tr><th>رمز عبور</th><td><?= htmlspecialchars($pass) ?></td></tr>
</table>
</div>

<div class="card p-3 shadow-sm">
<h5>وضعیت جداول:</h5>
<?php
$conn = getConnection(false);
$dbExists = $conn->select_db($dbName);
echo '<p>✅ دیتابیس '.($dbExists?'وجود دارد':'وجود ندارد').'</p>';
if ($dbExists) {
    $conn->select_db($dbName);
    $res = $conn->query("SHOW TABLES LIKE 'polls'");
    echo '<p>'.($res && $res->num_rows ? '✅ جدول polls وجود دارد' : '❌ جدول polls وجود ندارد').'</p>';
    $res2 = $conn->query("SHOW TABLES LIKE 'options'");
    echo '<p>'.($res2 && $res2->num_rows ? '✅ جدول options وجود دارد' : '❌ جدول options وجود ندارد').'</p>';
}
$conn->close();
?>
</div>
</div>

</body>
</html>
