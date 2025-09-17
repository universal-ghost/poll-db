<?php
// results.php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'my_survey_db';

$conn = new mysqli($host, $user, $pass, $dbName);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// گرفتن تمام نظرسنجی‌ها
$polls = [];
$res = $conn->query("SELECT * FROM polls ORDER BY id ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $polls[] = $row;
    }
}

// گرفتن گزینه‌ها و تعداد رای‌ها برای هر نظرسنجی
$allOptions = [];
foreach ($polls as $poll) {
    $res = $conn->query("SELECT * FROM options WHERE poll_id = " . $poll['id']);
    $options = [];
    $total_votes = 0;
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $options[] = $row;
            $total_votes += $row['votes'];
        }
    }
    $allOptions[$poll['id']] = ['options' => $options, 'total' => $total_votes];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تحلیل نتایج نظرسنجی‌ها</title>
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

/* کارت‌ها */
.total-votes-box {
    margin-top: 15px;
    padding: 10px 15px;
    background: #dff9fb;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    font-weight: bold;
    color: #130f40;
    font-size: 1.1rem;
}
.total-number {
    font-size: 1.3rem;
    margin-right: 5px;
    color: #00a8ff;
}

.card.poll-card {
    margin-bottom:20px;
    border-radius:12px;
    padding:20px;
    background:white;
    box-shadow:0 4px 15px rgba(0,0,0,0.07);
    transition: transform 0.3s ease;
}
.card.poll-card:hover {
    transform: translateY(-3px);
}
.poll-title {
    font-weight:bold;
    font-size:1.3rem;
    color:#2f3640;
    margin-bottom:15px;
}
.option-row {
    margin-bottom:15px;
}
.option-info {
    display:flex;
    justify-content:space-between;
    font-weight:500;
    margin-bottom:5px;
}
.custom-progress {
    height:20px;
    border-radius:10px;
    background:#f1f2f6;
    overflow:hidden;
}
.custom-bar {
    height:100%;
    width:0; /* شروع از صفر برای انیمیشن */
    transition: width 1.2s ease-in-out;
    border-radius:10px;
}
.total-votes {
    display:block;
    margin-top:10px;
    color:#888;
    font-size:0.9rem;
}
.no-options {
    color:#999;
}
</style>
</head>
<body>

<div class="sidebar">
    <h5><ion-icon name="analytics-outline"></ion-icon> تحلیل نتایج</h5>
    <a href="index.php" class="btn">
        <ion-icon name="home-outline"></ion-icon> بازگشت به صفحه اصلی
    </a>
</div>

<div class="content">
    <div class="header">
        <a href="index.php">تحلیل نتایج نظرسنجی‌ها</a>
    </div>

    <?php if(empty($polls)): ?>
        <div class="alert alert-info">هیچ نظرسنجی‌ای ایجاد نشده است.</div>
    <?php else: ?>
        <?php 
        $colors = ['#ff6b6b','#1dd1a1','#54a0ff','#feca57','#5f27cd'];
        foreach($polls as $poll): 
            $options = $allOptions[$poll['id']]['options'];
            $total_votes = $allOptions[$poll['id']]['total'];
        ?>
        <div class="card poll-card">
            <h5 class="poll-title"><?= htmlspecialchars($poll['question']) ?></h5>
            <?php if(empty($options)): ?>
                <p class="no-options">هیچ گزینه‌ای وجود ندارد.</p>
            <?php else: ?>
                <?php foreach($options as $index => $opt):
                    $percent = $total_votes ? round(($opt['votes'] / $total_votes) * 100) : 0;
                    $color = $colors[$index % count($colors)];
                ?>
                <div class="option-row">
                    <div class="option-info">
                        <span><?= htmlspecialchars($opt['option_text']) ?></span>
                        <span><?= $opt['votes'] ?> رای (<?= $percent ?>%)</span>
                    </div>
                    <div class="progress custom-progress">
                        <div class="progress-bar custom-bar" 
                             data-width="<?= $percent ?>" 
                             style="background-color: <?= $color ?>;">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
<div class="total-votes-box">
    <ion-icon name="people-outline" style="font-size:24px; margin-left:5px;"></ion-icon>
    <span class="total-number" data-count="<?= $total_votes ?>">0</span> رای کل
</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".custom-bar").forEach(function(bar) {
        let width = bar.getAttribute("data-width");
        bar.style.width = width + "%";
    });
});
</script>
<script>
function animateCount(el) {
    let countTo = parseInt(el.getAttribute('data-count'), 10);
    let count = 0;
    let step = Math.ceil(countTo / 100);
    let interval = setInterval(() => {
        count += step;
        if(count >= countTo) {
            count = countTo;
            clearInterval(interval);
        }
        el.textContent = count;
    }, 20);
}

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".custom-bar").forEach(function(bar) {
        let width = bar.getAttribute("data-width");
        bar.style.width = width + "%";
    });
    document.querySelectorAll(".total-number").forEach(animateCount);
});
</script>


</body>
</html>
