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

// Подключаем конфиг для API-ключа, IP и портов
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

// Получаем данные пользователя и TTL
$username = trim($_REQUEST['username'] ?? '');
$ttl = max(1,(int)($_REQUEST['ttl'] ?? 60));
$expire_at = time() + $ttl*60;

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

    // Обновляем TTL пользователя
    $stmt = $db->prepare('UPDATE users SET expire_at=? WHERE username=?');
    $stmt->execute([$expire_at, $username]);

    // Синхронизация с /etc/3proxy/passwd
    $allUsers = $db->query('SELECT username,password FROM users')->fetchAll(PDO::FETCH_ASSOC);
    $lines = [];
    foreach($allUsers as $u){
        $lines[] = "users {$u['username']}:CL:{$u['password']}";
    }
    file_put_contents('/etc/3proxy/passwd', implode(' ', $lines) . "\n");

    // Ответ API с данными для подключения
    echo json_encode([
        'success'=>true,
        'username'=>$username,
        'ttl_minutes'=>$ttl,
        'expire_at'=>$expire_at,
        'server'=>[
            'ip'=>$PROXY_SERVER_IP,
            'socks_port'=>$PROXY_SOCKS_PORT,
            'http_port'=>$PROXY_HTTP_PORT,
            'socks_url'=>"socks5://{$username}:{$user['password']}@{$PROXY_SERVER_IP}:{$PROXY_SOCKS_PORT}",
            'http_url'=>"http://{$username}:{$user['password']}@{$PROXY_SERVER_IP}:{$PROXY_HTTP_PORT}"
        ]
    ]);
} catch(Exception $e){
    echo json_encode(['error'=>$e->getMessage()]);
}
