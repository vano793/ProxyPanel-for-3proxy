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
// sync_core.php
// Чистая функция синхронизации: берет активных пользователей из БД,
// формирует строку "users ..." и записывает во временный файл, затем mv в /etc/3proxy/passwd
// Возвращает массив с информацией (count, rc_mv, out_mv, rc_srv, out_srv) — не выводит HTML.

function sync_users($options = []) {
    // $options: ['dbfile'=>..., 'authFile'=>..., 'tmpFile'=>...]
    $dbfile = $options['dbfile'] ?? __DIR__ . '/proxy.sqlite';
    $authFile = $options['authFile'] ?? '/etc/3proxy/passwd';
    $tmpFile = $options['tmpFile'] ?? '/tmp/.proxyauth.tmp';
    $result = [
        'success' => false,
        'count' => 0,
        'userStr' => '',
        'mv' => null,
        'mv_out' => [],
        'srv' => null,
        'srv_out' => [],
        'error' => null
    ];

    try {
        $db = new PDO('sqlite:' . $dbfile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $now = time();
        // Удаляем просроченные записи (опционально; можно убрать если не нужно)
        $stmt = $db->prepare("DELETE FROM users WHERE expire_at <= ?");
        $stmt->execute([$now]);

        $rows = $db->query("SELECT username, password FROM users WHERE expire_at > $now")->fetchAll(PDO::FETCH_ASSOC);

        $userArr = [];
        foreach ($rows as $r) {
            $u = trim($r['username']);
            $p = trim($r['password']); // предполагаем plain пароли
            if ($u !== '' && $p !== '') {
                // экранируем пробелы/непечатаемые — здесь просто формируем строку
                $userArr[] = $u . ":CL:" . $p;
            }
        }

        if (count($userArr) > 0) {
            $userStr = 'users ' . implode(' ', $userArr) . "\n";
        } else {
            $userStr = ''; // пустой — можно оставить пустой файл
        }

        $result['count'] = count($userArr);
        $result['userStr'] = $userStr;

        // Запись во временный файл (в каталоге, доступном для PHP)
        $written = @file_put_contents($tmpFile, $userStr, LOCK_EX);
        if ($written === false) {
            $result['error'] = "Не удалось записать временный файл $tmpFile";
            return $result;
        }
        @chmod($tmpFile, 0640);
        @chown($tmpFile, 'root');

        // Перемещение временного файла в /etc/3proxy/passwd (требуется sudo для mv)
        // Возвращаем вывод и код rc
        exec("sudo mv " . escapeshellarg($tmpFile) . " " . escapeshellarg($authFile) . " 2>&1", $out_mv, $rc_mv);
        $result['mv'] = $rc_mv;
        $result['mv_out'] = $out_mv;

        // Перезапуск 3proxy (по желанию)
        exec("sudo systemctl restart 3proxy 2>&1", $out_srv, $rc_srv);
        $result['srv'] = $rc_srv;
        $result['srv_out'] = $out_srv;

        $result['success'] = true;
        return $result;

    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
        return $result;
    }
}
