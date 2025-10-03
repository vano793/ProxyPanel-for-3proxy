<?php
/*
Proxy Panel — скрипт управления 3proxy пользователями
Разработка кода: ChatGPT (OpenAI)
Концепция и видение: Иван Михайлович
Лицензия: GNU General Public License v3.0 (GPL-3.0)
Ссылка на лицензию: https://www.gnu.org/licenses/gpl-3.0.html
*/
session_start();
if(!isset($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit;
}

$authFile = '/etc/3proxy/passwd';
$msg = "";
$content = "";

// Тестовые данные
$testUser = 'testuser';
$testPass = 'Test123456';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    try {
        // Получаем текущих пользователей из базы или файла
        $lines = file_exists($authFile) ? file($authFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        $usersArr = [];

        // Парсим существующую строку users
        if(count($lines)){
            foreach($lines as $line){
                if(strpos($line,'users')===0){
                    $parts = explode(' ',$line);
                    array_shift($parts); // убираем слово users
                    foreach($parts as $p){
                        $usersArr[] = $p;
                    }
                }
            }
        }

        // Добавляем тестового пользователя
        $exists = false;
        foreach($usersArr as $p){
            if(strpos($p,$testUser.":CL:")===0){
                $exists = true;
                break;
            }
        }
        if(!$exists){
            $usersArr[] = $testUser.":CL:".$testPass;
        }

        // Перезаписываем файл одной строкой
        $userStr = "users ".implode(" ", $usersArr);
        file_put_contents($authFile, $userStr."\n");

        // Перезапуск 3proxy
        exec("systemctl restart 3proxy");

        $msg = "Запись прошла успешно!";
        $content = $userStr;

    } catch (Exception $e) {
        $msg = "Ошибка записи: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Тест записи в 3proxy</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header>Proxy Panel - Тест записи</header>

<?php include 'menu.php'; ?>


<div class="container">
    <div class="card">
        <h2>Тест записи пользователя в 3proxy</h2>
        <?php if($msg) echo "<div class='msg'>$msg</div>"; ?>
        <form method="post">
            <button class="small-btn" type="submit">Добавить тестового пользователя</button>
        </form>
        <?php if($content): ?>
            <h3>Содержимое файла /etc/3proxy/passwd:</h3>
            <textarea readonly><?= htmlspecialchars($content) ?></textarea>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
