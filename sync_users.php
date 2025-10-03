<?php
/*
Proxy Panel — скрипт управления 3proxy пользователями
Разработка кода: ChatGPT (OpenAI)
Концепция и видение: Иван Михайлович
Лицензия: GNU General Public License v3.0 (GPL-3.0)
Ссылка на лицензию: https://www.gnu.org/licenses/gpl-3.0.html
*/
// sync_users.php (веб-страница)
session_start();
if(!($_SESSION['logged_in'] ?? false)){
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/sync_core.php';

$result = sync_users(); // использует proxy.sqlite и /etc/3proxy/passwd по умолчанию
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Синхронизация пользователей</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>Proxy Panel</header>
<?php include 'menu.php'; ?>
<div class="container">
  <div class="card">
    <h2>Синхронизация пользователей 3proxy</h2>
    <?php if(isset($result['error']) && $result['error']): ?>
      <p class="status-expired">Ошибка: <?=htmlspecialchars($result['error'])?></p>
    <?php else: ?>
      <p>Записано пользователей: <?=intval($result['count'])?></p>
      <p>Перемещение файла RC: <?=intval($result['mv'])?></p>
      <?php if(!empty($result['mv_out'])): ?>
        <pre><?=htmlspecialchars(implode("\n", $result['mv_out']))?></pre>
      <?php endif; ?>
      <p>Перезапуск 3proxy RC: <?=intval($result['srv'])?></p>
      <?php if(!empty($result['srv_out'])): ?>
        <pre><?=htmlspecialchars(implode("\n", $result['srv_out']))?></pre>
      <?php endif; ?>
    <?php endif; ?>
    <form method="post"><button type="submit">Обновить</button></form>
  </div>
</div>
</body>
</html>
