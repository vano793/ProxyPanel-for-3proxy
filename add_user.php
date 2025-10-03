<?php
/*
Proxy Panel — скрипт управления 3proxy пользователями
Разработка кода: ChatGPT (OpenAI)
Концепция и видение: Иван Михайлович
Лицензия: GNU General Public License v3.0 (GPL-3.0)
Ссылка на лицензию: https://www.gnu.org/licenses/gpl-3.0.html
*/

session_start();
if(!($_SESSION['logged_in'] ?? false)){ 
    header("Location: index.php"); 
    exit; 
}

$DB = __DIR__ . '/proxy.sqlite';
$authFile = '/etc/3proxy/passwd'; // Файл для 3proxy
$msg = '';

try {
    $db = new PDO('sqlite:' . $DB);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $ttl = max(1,(int)($_POST['ttl'] ?? 60));
        $expire_at = time() + $ttl*60;
        $username = trim($_POST['username']) ?: "user".rand(1000,9999);
        $password = trim($_POST['password']) ?: substr(bin2hex(random_bytes(6)),0,12);

        // Проверка существующего пользователя
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username=?");
        $stmt->execute([$username]);
        if($stmt->fetchColumn() > 0){
            $msg = "Пользователь с логином <b>$username</b> уже существует!";
        } else {
            // Вставка в БД
            $stmt = $db->prepare("INSERT INTO users (username,password,expire_at,created_at) VALUES (?,?,?,?)");
            $stmt->execute([$username,$password,$expire_at,time()]);

            // Синхронизация с файлом /etc/3proxy/passwd
            $rows = $db->query("SELECT username,password FROM users")->fetchAll(PDO::FETCH_ASSOC);
            $userArr = [];
            foreach($rows as $r){
                $userArr[] = $r['username'] . ":CL:" . $r['password'];
            }
            $content = "users " . implode(' ', $userArr) . "\n";
            file_put_contents($authFile, $content, LOCK_EX);
            chmod($authFile, 0640);
            chown($authFile, 'root');

            // Не вызываем include sync_users.php, синхронизируем прямо здесь
            $msg = "Создан пользователь <b>$username</b> с паролем <b>$password</b> (TTL: $ttl мин)";
        }
    }
} catch(Exception $e){
    $msg = "Ошибка: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Добавить пользователя</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>Proxy Panel</header>
<?php include 'menu.php'; ?>
<div class="container">
    <div class="card">
        <h2>Создать нового пользователя</h2>
        <?php if($msg) echo "<div class='msg'>$msg</div>"; ?>
        <form method="post">
            <label>Логин (оставьте пустым для автогенерации):</label>
            <input type="text" name="username">
            <label>Пароль (оставьте пустым для автогенерации):</label>
            <input type="text" name="password">
            <label>TTL (в минутах):</label>
            <input type="number" name="ttl" value="60" min="1" required>
            <button class="small-btn" type="submit">Создать пользователя</button>
        </form>
    </div>
</div>
</body>
</html>
