<?php
/*
Proxy Panel — скрипт управления 3proxy пользователями
Разработка кода: ChatGPT (OpenAI)
Концепция и видение: Иван Михайлович
Лицензия: GNU General Public License v3.0 (GPL-3.0)
Ссылка на лицензию: https://www.gnu.org/licenses/gpl-3.0.html
*/
header('Content-Type: application/json');
session_start();

$DB = __DIR__ . '/proxy.sqlite';

// Подключаем конфиг
if(file_exists(__DIR__.'/config.php')){
    include __DIR__.'/config.php';
} else {
    echo json_encode(['error'=>'Config file not found']);
    exit;
}

// Проверка API-ключа
$apiKey = $_REQUEST['key'] ?? '';
if($apiKey !== $API_KEY){
    http_response_code(403);
    echo json_encode(['error'=>'Invalid API key']);
    exit;
}

// Получаем имя пользователя для удаления
$username = trim($_REQUEST['username'] ?? '');
if(!$username){
    echo json_encode(['error'=>'Username required']);
    exit;
}

try {
    $db = new PDO('sqlite:'.$DB);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Проверяем существование пользователя
    $stmt = $db->prepare('SELECT * FROM users WHERE username=?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$user){
        echo json_encode(['error'=>"User $username not found"]);
        exit;
    }

    // Удаляем пользователя из базы
    $stmt = $db->prepare('DELETE FROM users WHERE username=?');
    $stmt->execute([$username]);

    // Синхронизация с /etc/3proxy/passwd
    $allUsers = $db->query('SELECT username,password FROM users')->fetchAll(PDO::FETCH_ASSOC);
    $lines = [];
    foreach($allUsers as $u){
        $lines[] = "users {$u['username']}:CL:{$u['password']}";
    }
    file_put_contents('/etc/3proxy/passwd', implode(' ', $lines) . "\n");

    echo json_encode([
        'success'=>true,
        'deleted_user'=>$username,
        'remaining_count'=>count($allUsers),
    ]);
} catch(Exception $e){
    echo json_encode(['error'=>$e->getMessage()]);
}
