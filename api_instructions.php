<?php
/*
Proxy Panel — скрипт управления 3proxy пользователями
Разработка кода: ChatGPT (OpenAI)
Концепция и видение: Иван Михайлович
Лицензия: GNU General Public License v3.0 (GPL-3.0)
Ссылка на лицензию: https://www.gnu.org/licenses/gpl-3.0.html
*/
session_start();
if(!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']){
    header("Location: index.php");
    exit;
}

// Подключаем конфиг
$serverDisplay = '127.0.0.1';
$socksPort = 1080;
$httpPort = 3128;
$apiKey = 'YOUR_API_KEY';

if (file_exists(__DIR__ . '/config.php')) {
    include __DIR__ . '/config.php';
    if (!empty($PROXY_SERVER_IP)) $serverDisplay = $PROXY_SERVER_IP;
    if (!empty($PROXY_SOCKS_PORT)) $socksPort = $PROXY_SOCKS_PORT;
    if (!empty($PROXY_HTTP_PORT)) $httpPort = $PROXY_HTTP_PORT;
    if (!empty($API_KEY)) $apiKey = $API_KEY;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<title>Инструкция API — Proxy Panel</title>
<link rel="stylesheet" href="style.css">

<!-- CodeMirror -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/neo.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/shell/shell.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/php/php.min.js"></script>

<style>
.container{max-width:1000px;padding:20px;margin:auto;}
.card{padding:20px;margin-top:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.08);background:#fff;}
.CodeMirror { border:1px solid #e0e0e0; border-radius:6px; font-size:13px; height:auto; }
.param-table { width:100%; border-collapse:collapse; margin-top:10px; }
.param-table th, .param-table td { border:1px solid #eee; padding:8px; text-align:left; }
.param-table th { background:#fafafa; }
small.hint { color:#666; }
</style>
</head>
<body>
<header>Proxy Panel - Инструкция API</header>
<?php include 'menu.php'; ?>

<div class="container">
  <div class="card">
    <h2>API: краткое описание</h2>
    <p>API позволяет управлять временными пользователями для 3proxy: <b>создавать</b>, <b>удалять</b> и <b>обновлять TTL</b>.  
    Вызовы поддерживают GET и POST.</p>
  
 
    <h2>Параметры API</h2>
    <p>Все вызовы API принимают следующие параметры:</p>
    <table class="param-table">
      <thead>
        <tr>
          <th>Параметр</th>
          <th>Обязательный</th>
          <th>Описание</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><code>key</code></td>
          <td>Да</td>
          <td>API-ключ для аутентификации. Обязателен для всех вызовов.</td>
        </tr>
        <tr>
          <td><code>username</code></td>
          <td>Нет для создания, Да для удаления/обновления</td>
          <td>Имя пользователя. Если не указано при создании — генерируется автоматически.</td>
        </tr>
        <tr>
          <td><code>password</code></td>
          <td>Нет</td>
          <td>Пароль пользователя. Если не указан — генерируется случайный 12-символьный.</td>
        </tr>
        <tr>
          <td><code>ttl</code></td>
          <td>Нет</td>
          <td>Время жизни пользователя в минутах. По умолчанию 60. Максимум 1440.</td>
        </tr>
        <tr>
          <td><code>username</code> (для удаления/обновления)</td>
          <td>Да</td>
          <td>Логин пользователя, которого нужно удалить или обновить TTL.</td>
        </tr>
      </tbody>
    </table>
    <p class="hint"><small>В ответах API возвращаются поля <code>server.ip</code>, <code>server.socks_port</code>, <code>server.http_port</code> и готовые ссылки <code>socks_url</code>, <code>http_url</code>.</small></p>
  </div>

  <div class="card">
    <h2>1) Создание пользователя</h2>
    <p>Эндпоинт: <code>/api_create_user.php</code></p>

    <p>GET-запрос:</p>
    <textarea id="create-get"><?php echo "http://{$serverDisplay}/proxypanel/api_create_user.php?key={$apiKey}&ttl=60&username=test1&password=pass1"; ?></textarea>

    <p>POST-запрос:</p>
    <textarea id="create-post"><?php echo "curl -X POST 'http://{$serverDisplay}/proxypanel/api_create_user.php' \\\n  -d 'key={$apiKey}' \\\n  -d 'ttl=60' \\\n  -d 'username=test2' \\\n  -d 'password=pass2'"; ?></textarea>

    <p>Пример прокси:</p>
    <ul>
        <li>SOCKS5: <code><?="socks5://test2:pass2@{$serverDisplay}:{$socksPort}"?></code></li>
        <li>HTTP: <code><?="http://test2:pass2@{$serverDisplay}:{$httpPort}"?></code></li>
    </ul>
  </div>

  <div class="card">
    <h2>2) Удаление пользователя</h2>
    <p>Эндпоинт: <code>/api_delete_user.php</code></p>

    <p>GET-запрос:</p>
    <textarea id="delete-get"><?php echo "http://{$serverDisplay}/proxypanel/api_delete_user.php?key={$apiKey}&username=test1"; ?></textarea>

    <p>POST-запрос:</p>
    <textarea id="delete-post"><?php echo "curl -X POST 'http://{$serverDisplay}/proxypanel/api_delete_user.php' \\\n  -d 'key={$apiKey}' \\\n  -d 'username=test1'"; ?></textarea>
  </div>

  <div class="card">
    <h2>3) Обновление TTL пользователя</h2>
    <p>Эндпоинт: <code>/api_update_user.php</code></p>

    <p>GET-запрос:</p>
    <textarea id="update-get"><?php echo "http://{$serverDisplay}/proxypanel/api_update_user.php?key={$apiKey}&username=test1&ttl=120"; ?></textarea>

    <p>POST-запрос:</p>
    <textarea id="update-post"><?php echo "curl -X POST 'http://{$serverDisplay}/proxypanel/api_update_user.php' \\\n  -d 'key={$apiKey}' \\\n  -d 'username=test1' \\\n  -d 'ttl=120'"; ?></textarea>
  </div>
</div>

<script>
document.querySelectorAll('textarea').forEach(function(textarea){
  var txt = textarea.value.trim();
  var mode = "javascript";
  if (/^curl|^http/i.test(txt)) mode = "shell";
  if (/^\{/.test(txt)) mode = "javascript";
  if (/<\?php/.test(txt)) mode = "application/x-httpd-php";

  CodeMirror.fromTextArea(textarea, {
    mode: mode,
    theme: "neo",
    lineNumbers: false,
    readOnly: true,
    viewportMargin: Infinity
  });
});
</script>
</body>
</html>
