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
body{font-family:Arial;background:#f4f6f8;margin:0;padding:0;}
.container{max-width:400px;margin:100px auto;padding:20px;background:white;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);}
input,button{width:100%;padding:10px;margin:5px 0;box-sizing:border-box;}
button{background:#3498db;color:white;border:none;border-radius:5px;cursor:pointer;}
button:hover{background:#2980b9;}
.error{color:red;margin-bottom:10px;}
</style>
</head>
<body>
<div class="container">
<h2>Вход в Proxy Panel</h2>
<?php if($error) echo "<div class='error'>$error</div>"; ?>
<form method="post">
<input type="text" name="login" placeholder="Логин" required>
<input type="password" name="password" placeholder="Пароль" required>
<button type="submit">Войти</button>
</form>
</div>
</body>
</html>