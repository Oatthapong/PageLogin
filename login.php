<?php
declare(strict_types=1);

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

header('Content-Type: text/html; charset=UTF-8');

function escape_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$remember = isset($_POST['remember']);

$errors = [];

if ($email === '') {
    $errors[] = 'กรุณากรอกอีเมล';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
}

if ($password === '') {
    $errors[] = 'กรุณากรอกรหัสผ่าน';
}

// ตัวอย่างสำหรับหน้าเดโม ควรเปลี่ยนส่วนนี้เป็นการตรวจสอบจากฐานข้อมูลจริง
$demoUser = [
    'email' => 'admin@example.com',
    'password' => '123456',
    'display_name' => 'Administrator',
];

$status = 'error';
$statusLabel = 'Login Failed';
$pageTitle = 'เข้าสู่ระบบไม่สำเร็จ';
$message = 'ตรวจสอบข้อมูลแล้วลองใหม่อีกครั้ง';

if ($errors === []) {
    $emailMatches = strcasecmp($email, $demoUser['email']) === 0;
    $passwordMatches = hash_equals($demoUser['password'], $password);

    if ($emailMatches && $passwordMatches) {
        session_regenerate_id(true);

        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_email'] = $demoUser['email'];
        $_SESSION['display_name'] = $demoUser['display_name'];
        $_SESSION['remember_me'] = $remember;

        $status = 'success';
        $statusLabel = 'Login Success';
        $pageTitle = 'เข้าสู่ระบบสำเร็จ';
        $message = 'ข้อมูลถูกส่งจากฟอร์มเรียบร้อยแล้ว และระบบได้บันทึก session ตัวอย่างไว้ให้';
    } else {
        unset(
            $_SESSION['is_logged_in'],
            $_SESSION['user_email'],
            $_SESSION['display_name'],
            $_SESSION['remember_me']
        );

        $errors[] = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
        http_response_code(401);
    }
} else {
    http_response_code(422);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape_html($pageTitle); ?></title>
    <link rel="stylesheet" href="login.css">
</head>
<body class="result-page">
    <main class="result-card">
        <span class="status-pill <?= $status === 'success' ? 'success' : 'error'; ?>">
            <?= escape_html($statusLabel); ?>
        </span>

        <h1><?= escape_html($pageTitle); ?></h1>
        <p><?= escape_html($message); ?></p>

        <section class="summary-grid" aria-label="สรุปข้อมูลการเข้าสู่ระบบ">
            <article class="summary-item">
                <strong>อีเมลที่ส่งมา</strong>
                <span class="mono-text"><?= escape_html($email !== '' ? $email : '-'); ?></span>
            </article>
            <article class="summary-item">
                <strong>จดจำการเข้าสู่ระบบ</strong>
                <span><?= $remember ? 'เปิดใช้งาน' : 'ไม่ได้เลือก'; ?></span>
            </article>
            <article class="summary-item">
                <strong>สถานะ Session</strong>
                <span><?= $status === 'success' ? 'สร้างเรียบร้อย' : 'ยังไม่ถูกสร้าง'; ?></span>
            </article>
        </section>

        <?php if ($errors !== []) : ?>
            <section aria-label="ข้อผิดพลาด">
                <ul class="note-list">
                    <?php foreach ($errors as $error) : ?>
                        <li><?= escape_html($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

        <div class="result-actions">
            <a href="login.html" class="primary-link">กลับไปหน้า Login</a>
        </div>
    </main>
</body>
</html>
