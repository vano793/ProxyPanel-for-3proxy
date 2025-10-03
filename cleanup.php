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

// Проверяем авторизацию
if(!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']){
    header("Location: index.php");
    exit;
}

$DB = __DIR__ . '/proxy.sqlite';
$authFile = '/etc/3proxy/passwd';
$now = time();

try {
    $db = new PDO('sqlite:' . $DB);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Удаляем просроченных пользователей из базы
    $stmt = $db->prepare('DELETE FROM users WHERE expire_at <= ?');
    $stmt->execute([$now]);

    // Получаем оставшихся активных пользователей
    $rows = $db->query("SELECT username, password FROM users")->fetchAll(PDO::FETCH_ASSOC);

    $userArr = [];
    foreach($rows as $r){
        $userArr[] = $r['username'] . ":CL:" . $r['password'];
    }

    // Формируем строку для /etc/3proxy/passwd
    $userStr = "users " . implode(' ', $userArr) . "\n";
    file_put_contents('/tmp/.proxyauth.tmp', $userStr, LOCK_EX);
    chmod('/tmp/.proxyauth.tmp', 0640);
    chown('/tmp/.proxyauth.tmp', 'root');
    
    // Перемещаем временный файл и перезапускаем 3proxy
    exec("sudo mv /tmp/.proxyauth.tmp $authFile 2>&1", $out_mv, $rc_mv);
    exec("sudo systemctl restart 3proxy 2>&1", $out_srv, $rc_srv);

    echo "Очистка завершена. Удалено просроченных пользователей: ".$stmt->rowCount()."\n";
    echo "RC mv=$rc_mv, RC restart=$rc_srv\n";

} catch(Exception $e){
    echo "Ошибка: ".$e->getMessage();
}