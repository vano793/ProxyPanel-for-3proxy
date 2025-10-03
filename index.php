<?php
/*
Proxy Panel — скрипт управления 3proxy пользователями
Разработка кода: ChatGPT (OpenAI)
Концепция и видение: Иван Михайлович
Лицензия: GNU General Public License v3.0 (GPL-3.0)
Ссылка на лицензию: https://www.gnu.org/licenses/gpl-3.0.html
*/
session_start();
// Подключаем конфигурационный файл
require_once __DIR__ . '/config.php';

$DB = __DIR__ . '/proxy.sqlite';
$db = new PDO('sqlite:' . $DB);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = '';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $login = $_POST['login'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Проверка с использованием данных из config.php
    if($login === $ADMIN_LOGIN && $pass === $ADMIN_PASSWORD){
        $_SESSION['logged_in'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Неверный логин или пароль";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Proxy Panel - Вход</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
.container {
    max-width: 400px;
    width: 90%;
    padding: 30px 25px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.logo-container {
    text-align: center;
    margin-bottom: 25px;
}
.logo {
    max-width: 100%;
    height: auto;
    max-height: 70px;
}
input, button {
    width: 100%;
    padding: 12px 15px;
    margin: 8px 0;
    box-sizing: border-box;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}
input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
}
button {
    background: #3498db;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s ease;
}
button:hover {
    background: #2980b9;
}
.error {
    color: #e74c3c;
    margin-bottom: 15px;
    padding: 10px;
    background: #fdf2f2;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    text-align: center;
    font-size: 14px;
}
</style>
</head>
<body>
<div class="container">
    <div class="logo-container">
        <img src="img/logo.jpg" alt="Proxy Panel Logo" class="logo">
    </div>
    <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="login" placeholder="Логин" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Войти</button>
    </form>
</div>
</body>
</html>
