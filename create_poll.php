<?php
// create_poll.php

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'my_survey_db';

// اتصال به دیتابیس
$conn = new mysqli($host, $user, $pass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$messages = [];

// ذخیره نظرسنجی جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');
    $options = $_POST['options'] ?? [];

    if (!$question) {
        $messages[] = "❌ عنوان نظرسنجی نمی‌تواند خالی باشد.";
    } elseif (count($options) < 2) {
        $messages[] = "❌ حداقل دو گزینه لازم است.";
    } else {
        // اضافه کردن نظرسنجی
        $stmt = $conn->prepare("INSERT INTO polls (question) VALUES (?)");
        $stmt->bind_param("s", $question);
        if ($stmt->execute()) {
            $poll_id = $stmt->insert_id;

            // اضافه کردن گزینه‌ها
            $stmt_opt = $conn->prepare("INSERT INTO options (poll_id, option_text) VALUES (?, ?)");
            foreach ($options as $opt) {
                $opt = trim($opt);
                if ($opt !== '') {
                    $stmt_opt->bind_param("is", $poll_id, $opt);
                    $stmt_opt->execute();
                }
            }
            $messages[] = "✅ نظرسنجی با موفقیت ایجاد شد!";
        } else {
            $messages[] = "❌ خطا در ایجاد نظرسنجی: " . $conn->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ایجاد نظرسنجی جدید</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ionicons -->
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
        .card { margin-bottom:20px; border-radius:8px; padding:20px; }
        .msg { margin:10px 0; font-weight:bold; }
        .msg-success { color:green; }
        .msg-error { color:red; }
        .option-input { margin-bottom:10px; }
    </style>
    <script>
        function addOption() {
            const container = document.getElementById('optionsContainer');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'options[]';
            input.placeholder = 'متن گزینه';
            input.className = 'form-control option-input';
            container.appendChild(input);
        }
    </script>
</head>
<body>

<div class="sidebar">
    <h5><ion-icon name="create-outline"></ion-icon> نظرسنجی جدید</h5>
    <a href="index.php" class="btn">
        <ion-icon name="home-outline"></ion-icon> صفحه اصلی
    </a>
    <a href="setup.php" class="btn">
        <ion-icon name="settings-outline"></ion-icon> داشبورد
    </a>
</div>

<div class="content">
    <div class="header">
        <a href="index.php">ایجاد نظرسنجی جدید</a>
    </div>

    <?php foreach ($messages as $msg): ?>
        <?php $cls = strpos($msg, '✅') === 0 ? 'msg-success' : 'msg-error'; ?>
        <div class="msg <?= $cls ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endforeach; ?>

    <div class="card shadow-sm">
        <form method="post">
            <div class="mb-3">
                <label>عنوان نظرسنجی:</label>
                <input type="text" name="question" class="form-control" required>
            </div>
            <div id="optionsContainer">
                <input type="text" name="options[]" placeholder="گزینه 1" class="form-control option-input" required>
                <input type="text" name="options[]" placeholder="گزینه 2" class="form-control option-input" required>
            </div>
            <button type="button" class="btn btn-secondary mb-3" onclick="addOption()">
                <ion-icon name="add-circle-outline"></ion-icon> افزودن گزینه
            </button>
            <br>
            <button type="submit" class="btn btn-primary">
                <ion-icon name="checkmark-circle-outline"></ion-icon> ایجاد نظرسنجی
            </button>
        </form>
    </div>
</div>

</body>
</html>
