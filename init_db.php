<?php
/*
Proxy Panel — скрипт управления 3proxy пользователями
Разработка кода: ChatGPT (OpenAI)
Концепция и видение: Иван Михайлович
Лицензия: GNU General Public License v3.0 (GPL-3.0)
Ссылка на лицензию: https://www.gnu.org/licenses/gpl-3.0.html
*/
$dbfile = __DIR__ . '/proxy.sqlite';
$db = new PDO('sqlite:' . $dbfile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  password TEXT NOT NULL,
  expire_at INTEGER NOT NULL,
  created_at INTEGER NOT NULL
)");
echo "DB init OK\n";
