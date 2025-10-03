<?php
/*
Proxy Panel — скрипт управления 3proxy пользователями
Разработка кода: ChatGPT (OpenAI)
Концепция и видение: Иван Михайлович
Лицензия: GNU General Public License v3.0 (GPL-3.0)
Ссылка на лицензию: https://www.gnu.org/licenses/gpl-3.0.html
*/
require_once "config.php";

// Проверка API-ключа
if (!isset($_GET['key']) || $_GET['key'] !== $API_KEY) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Invalid API key"]);
    exit;
}

// Получаем параметры
$username = isset($_GET['username']) ? $_GET['username'] : ("user" . rand(1000, 9999));
$password = isset($_GET['password']) ? $_GET['password'] : bin2hex(random_bytes(5));
$ttl      = isset($_GET['ttl']) ? intval($_GET['ttl']) : 60; // минут

$expireAt = time() + ($ttl * 60);

// Тут вставляется твоя логика записи пользователя в 3proxy (мы эмулируем успешную запись)
$syncResult = [
    "success" => true,
    "count"   => 1,
    "error"   => null
];

// Формируем JSON-ответ
$response = [
    "success"     => true,
    "username"    => $username,
    "password"    => $password,
    "ttl_minutes" => $ttl,
    "expire_at"   => $expireAt,
    "server" => [
        "ip"    => $PROXY_SERVER_IP,
        "socks" => $PROXY_SOCKS_PORT,
        "http"  => $PROXY_HTTP_PORT
    ],
    "sync" => $syncResult
];

// Отдаём JSON
header("Content-Type: application/json");
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
