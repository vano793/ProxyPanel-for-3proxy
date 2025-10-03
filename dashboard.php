<?php
/*
Proxy Panel — скрипт управления 3proxy пользователями
Разработка кода: ChatGPT (OpenAI)
Концепция и видение: Иван Михайлович
Лицензия: GNU General Public License v3.0 (GPL-3.0)
Ссылка на лицензию: https://www.gnu.org/licenses/gpl-3.0.html
*/
session_start();
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    header("Location: index.php");
    exit;
}

// Подключение к базе
$DB = __DIR__ . '/proxy.sqlite';
$db = new PDO('sqlite:' . $DB);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Статистика
$now = time();
$total = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$active = $db->query("SELECT COUNT(*) FROM users WHERE expire_at > $now")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Proxy Panel — Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header>Proxy Panel</header>

<?php include 'menu.php'; ?>

<div class="container">
    <div class="card">
        <h2>Статистика пользователей</h2>
        <p>Всего пользователей: <b><?= $total ?></b></p>
        <p>Активных пользователей: <b><?= $active ?></b></p>
    </div>
</div>

</body>
</html>
