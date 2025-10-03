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

// Подключаем конфигурационный файл
require_once __DIR__ . '/config.php';

$DB = __DIR__ . '/proxy.sqlite';
$authFile = '/etc/3proxy/passwd';

// Используем настройки из config.php
$serverIP = $PROXY_SERVER_IP;
$socksPort = $PROXY_SOCKS_PORT;
$httpPort = $PROXY_HTTP_PORT;

$db = new PDO('sqlite:' . $DB);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Удаление пользователя (POST для безопасности)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $delUser = $_POST['delete_user'];
    $stmt = $db->prepare("DELETE FROM users WHERE username = ?");
    $stmt->execute([$delUser]);

    // синхронизируем файл passwd после удаления
    $now = time();
    $rows = $db->query("SELECT username,password FROM users WHERE expire_at > $now")->fetchAll(PDO::FETCH_ASSOC);
    $userArr = [];
    foreach ($rows as $r) {
        $userArr[] = $r['username'] . ":CL:" . $r['password'];
    }
    $userStr = count($userArr) ? 'users ' . implode(' ', $userArr) . "\n" : '';
    @file_put_contents($authFile, $userStr, LOCK_EX);

    @exec('sudo systemctl restart 3proxy 2>&1', $out, $rc);

    header('Location: users.php');
    exit;
}

// Получаем пользователей
$now = time();
$users = $db->query("SELECT username,password,expire_at,created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<title>Список пользователей</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>Proxy Panel - Список пользователей</header>
<?php include 'menu.php'; ?>

<div class="container">
    <div class="card">
        <h2>Все пользователи</h2>

        <table>
            <thead>
                <tr>
                    <th>Логин</th>
                    <th>Пароль</th>
                    <th>TTL (мин)</th>
                    <th>Статус</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($users)): ?>
                <tr><td colspan="5">Пользователей нет</td></tr>
            <?php else: ?>
                <?php foreach($users as $u):
                    $ttl = ceil(($u['expire_at'] - time())/60);
                    $statusClass = ($u['expire_at'] > time()) ? 'status-active' : 'status-expired';
                    $statusText = ($u['expire_at'] > time()) ? 'Активен' : 'Просрочен';

                    $username = htmlspecialchars($u['username'], ENT_QUOTES);
                    $password = htmlspecialchars($u['password'], ENT_QUOTES);

                    $socksLink = "socks5://{$username}:{$password}@{$serverIP}:{$socksPort}";
                    $httpLink  = "http://{$username}:{$password}@{$serverIP}:{$httpPort}";
                    $copyIdSocks = 'copy_' . preg_replace('/[^a-zA-Z0-9_\-]/','', $u['username']) . '_socks';
                    $copyIdHttp  = 'copy_' . preg_replace('/[^a-zA-Z0-9_\-]/','', $u['username']) . '_http';
                ?>
                <tr>
                    <td><?= $username ?></td>
                    <td><?= $password ?></td>
                    <td><?= max(0,$ttl) ?></td>
                    <td class="<?= $statusClass ?>"><?= $statusText ?></td>
                    <td>
                        <?php if($ttl > 0): ?>
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="delete_user" value="<?= $username ?>">
                            <button type="submit">Удалить</button>
                        </form>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <td colspan="5" style="text-align:left;padding-top:10px;padding-bottom:15px;">
                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            <!-- SOCKS5 прокси -->
                            <div style="flex: 1;">
                                <textarea id="<?= $copyIdSocks ?>" rows="1" style="height:15px;width:100%;font-family:monospace;font-size:11px;resize:none;padding:1px 4px;line-height:1.2;" readonly><?= htmlspecialchars($socksLink, ENT_QUOTES) ?></textarea>
                                <button onclick="copyText('<?= $copyIdSocks ?>')" style="margin-top:4px;font-size:11px;padding:2px 6px;">Копировать SOCKS5</button>
                            </div>
                            
                            <!-- HTTP прокси -->
                            <div style="flex: 1;">
                                <textarea id="<?= $copyIdHttp ?>" rows="1" style="height:15px;width:100%;font-family:monospace;font-size:11px;resize:none;padding:1px 4px;line-height:1.2;" readonly><?= htmlspecialchars($httpLink, ENT_QUOTES) ?></textarea>
                                <button onclick="copyText('<?= $copyIdHttp ?>')" style="margin-top:4px;font-size:11px;padding:2px 6px;">Копировать HTTP</button>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function copyText(id){
    var el = document.getElementById(id);
    el.select();
    el.setSelectionRange(0, 99999);
    try {
        var ok = document.execCommand('copy');
        if(ok){
            alert('Ссылка скопирована!');
        } else {
            alert('Не удалось скопировать, выделите текст вручную');
        }
    } catch(e){
        alert('Ваш браузер не поддерживает автоматическое копирование');
    }
}
</script>
</body>
</html>