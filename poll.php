<?php
// poll.php

session_start(); // برای ذخیره پیام

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'my_survey_db';

$conn = new mysqli($host, $user, $pass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$poll_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($poll_id <= 0) die("شناسه نظرسنجی معتبر نیست.");

$res = $conn->query("SELECT * FROM polls WHERE id = $poll_id");
if ($res && $res->num_rows > 0) $poll = $res->fetch_assoc();
else die("نظرسنجی یافت نشد.");

$options = [];
$res = $conn->query("SELECT * FROM options WHERE poll_id = $poll_id");
if ($res) {
    while ($row = $res->fetch_assoc()) $options[] = $row;
}

// ثبت رای
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['option_id'])) {
    $option_id = intval($_POST['option_id']);
    $stmt = $conn->prepare("UPDATE options SET votes = votes + 1 WHERE id = ? AND poll_id = ?");
    $stmt->bind_param("ii", $option_id, $poll_id);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['message'] = "✅ رای شما با موفقیت ثبت شد!";
    header("Location: index.php"); // بازگشت به صفحه اصلی
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($poll['question']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script type="module" src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.js"></script>
<style>
body { margin:0; padding:0; font-family:sans-serif; background:#f5f6fa; }
.sidebar { width: 220px; background: #2f3640; height: 100vh; position: fixed; color:white; display:flex; flex-direction:column; padding-top:20px; }
.sidebar h5 { text-align:center; margin-bottom:20px; display:flex; align-items:center; justify-content:center; }
.sidebar h5 ion-icon { margin-left:10px; font-size:24px; }
.sidebar .btn { width: 90%; margin:5px auto; display:flex; align-items:center; justify-content:flex-start; color:white; border:none; background:#353b48; padding:10px 15px; border-radius:8px; transition:0.3s; text-decoration:none; }
.sidebar .btn:hover { background:#40739e; }
.sidebar ion-icon { margin-left:10px; font-size:20px; }
.content { margin-right: 220px; padding:20px; }
.header { height:60px; background:#273c75; color:white; display:flex; align-items:center; padding:0 20px; border-radius:8px; margin-bottom:20px; }
.header a { color:white; text-decoration:none; font-weight:bold; font-size:1.2rem; }
.card { margin-bottom:20px; border-radius:8px; cursor:pointer; padding:20px; background:white; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
.option-btn { width:100%; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center; padding:10px 15px; border-radius:8px; border:1px solid #dcdde1; background:#f1f2f6; cursor:pointer; transition:0.3s; }
.option-btn:hover { background:#dcdde1; }
.back-btn { margin-bottom:20px; display:inline-block; }
</style>
</head>
<body>

<div class="sidebar">
    <h5><ion-icon name="home-outline"></ion-icon> داشبورد</h5>
    <a href="index.php" class="btn">
        <ion-icon name="arrow-back-outline"></ion-icon> بازگشت به صفحه اصلی
    </a>
</div>

<div class="content">
    <div class="header">
        <a href="index.php">نظرسنجی: <?= htmlspecialchars($poll['question']) ?></a>
    </div>

    <div class="card">
        <h5>لطفاً گزینه مورد نظر خود را انتخاب کنید:</h5>
        <form method="post">
            <?php foreach($options as $option): ?>
                <button type="submit" name="option_id" value="<?= $option['id'] ?>" class="option-btn">
                    <?= htmlspecialchars($option['option_text']) ?>
                </button>
            <?php endforeach; ?>
        </form>
    </div>
</div>

</body>
</html>
