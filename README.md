# Proxy Panel

Proxy Panel — скрипт для управления временными пользователями 3proxy.

**Разработка кода:** ChatGPT (OpenAI)  
**Концепция и видение:** Иван Михайлович  

## Лицензия
Этот проект распространяется под лицензией **GNU General Public License v3.0 (GPL-3.0)**.  
Вы можете использовать, изменять и распространять его, но любые модификации должны оставаться под GPL v3.

Ссылка на лицензию: [https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html)

Отлично! Вот полный вариант инструкции с учётом твоих пожеланий: сначала вводная о создателе кода и функционале, затем установка и настройка.

---
Фото/Screenshot
![Авторизация](https://github.com/vano793/ProxyPanel-for-3proxy/blob/main/screenshot/2025-10-04_13-40-41.png?raw=true)
![Список пользователей](https://github.com/vano793/ProxyPanel-for-3proxy/blob/main/screenshot/2025-10-04_14-07-39.png?raw=true)
![Создать пользователя](https://github.com/vano793/ProxyPanel-for-3proxy/blob/main/screenshot/2025-10-04_14-08-01.png?raw=true)
![Список пользователей](https://github.com/vano793/ProxyPanel-for-3proxy/blob/main/screenshot/2025-10-04_14-08-22.png?raw=true)
![Тест записи](https://github.com/vano793/ProxyPanel-for-3proxy/blob/main/screenshot/2025-10-04_14-11-08.png?raw=true)
![Инструкция API](https://github.com/vano793/ProxyPanel-for-3proxy/blob/main/screenshot/2025-10-04_14-11-32.png?raw=true)



# **Proxy Panel — Инструкция**

> ⚠️ Важно: Я не программист. Весь код этого скрипта был разработан мной совместно с ChatGPT (GPT-5-mini), по моему видению интерфейса и функционала.

---

## **Функционал скрипта**

Proxy Panel — это веб-панель для управления временными пользователями прокси 3proxy.

Основные возможности:

1. **Создание пользователей**

   * Логин и пароль можно задать вручную или генерировать автоматически.
   * Можно задать TTL (время жизни) пользователя в минутах.

2. **Синхронизация с 3proxy**

   * Пользователи добавляются в файл `/etc/3proxy/passwd`.
   * При необходимости автоматически удаляются просроченные пользователи.
   * Перезапуск 3proxy выполняется после обновления пользователей.

3. **Список пользователей**

   * Просмотр всех активных пользователей.
   * Статус активен/просрочен.

4. **API для интеграции**

   * Создание, удаление и обновление TTL пользователей через GET и POST запросы.
   * Поддержка передачи логина, пароля и TTL.
   * API использует ключ для безопасности (`API_KEY`) и отображает ссылки вида `socks5://username:password@IP:PORT` и `http://username:password@IP:PORT`.

5. **Дополнительно**

   * Лёгкий и минималистичный дизайн с адаптацией под CodeMirror для отображения примеров API.
   * Логи и пароли задаются произвольно и передаются через API.

---

## **Установка и настройка**

### **Шаг 0: Подготовка сервера**

Если 3proxy уже установлен, необходимо установить веб-сервер для работы панели:

```bash
sudo apt update
sudo apt install apache2 php php-sqlite3 curl -y
```

Проверьте, что порты Apache (80, 443) свободны:

```bash
ss -tulpn | grep -E '80|443'
```

---

### **Шаг 1: Установка веб-панели**

1. Создайте директорию для панели:

```bash
sudo mkdir -p /var/www/proxypanel
sudo chown -R www-data:www-data /var/www/proxypanel
```

2. Скопируйте все файлы панели в эту директорию:

```
img/
index.php
dashboard.php
add_user.php
users.php
sync_users.php
test_write.php
api_create_user.php
api_delete_user.php
api_update_user.php
api_instruction.php
menu.php
style.css
config.php
init_db.php
```

3. Проверьте права:

```bash
sudo chown -R www-data:www-data /var/www/proxypanel
sudo chmod -R 755 /var/www/proxypanel
```

---

### **Шаг 2: Настройка конфигурации панели**

1. Создайте `config.php`:

```php
<?php
// API-ключ
$API_KEY = "ваш_секретный_ключ";

// IP и порты прокси (совпадают с конфигом 3proxy)
$PROXY_SERVER_IP   = "ваш_внешний_IP"; 
$PROXY_SOCKS_PORT  = 1080;
$PROXY_HTTP_PORT   = 3128;
// Данные для входа админа
$ADMIN_LOGIN = "admin";
$ADMIN_PASSWORD = "admin";
?>
```

2. Инициализация базы пользователей:
   Откройте файл `init_db.php` через браузер один раз, чтобы создать таблицу `users`.

---

### **Шаг 3: Настройка Apache**

1. Создайте виртуальный хост:

```bash
sudo nano /etc/apache2/sites-available/proxypanel.conf
```

Содержимое:

```
<VirtualHost *:80>
    ServerAdmin admin@example.com
    DocumentRoot /var/www/proxypanel
    ServerName ваш_домен_или_IP

    <Directory /var/www/proxypanel>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/proxypanel_error.log
    CustomLog ${APACHE_LOG_DIR}/proxypanel_access.log combined
</VirtualHost>
```
2. Закройте доступ к базе данных:
   
```bash
sudo nano /etc/apache2/apache2.conf    # для Ubuntu/Debian
sudo nano /etc/httpd/conf/httpd.conf   # для CentOS/RHEL
```
и вставьте данный код:
```bash
<FilesMatch "\.sqlite$">
    Require all denied
</FilesMatch>

```
3. Активируйте сайт и перезапустите Apache:

```bash
sudo a2ensite proxypanel.conf
sudo systemctl reload apache2
```

---

### **Шаг 4: Настройка файлов для работы с 3proxy**

1. В конфиге `/etc/3proxy/3proxy.cfg` должны быть строки:

```
#Аутентификация
auth strong
#Используем внешний фаил для логинов и паролей
include /etc/3proxy/passwd
socks -p1080
http -p3128
```

2. Дайте права на перезапуск 3proxy без запроса пароля для веб-сервера
откройте 
```bash
sudo visudo
```
вставьте:
```bash
www-data ALL=(ALL) NOPASSWD: /bin/mv, /bin/chown, /bin/chmod, /bin/systemctl restart 3proxy
```

---

### **Шаг 5: Настройка cron для синхронизации**

Для автоматического удаления просроченных пользователей:

```bash
sudo crontab -u www-data -e
```

Добавьте:

```bash
*/2 * * * * /usr/bin/php /var/www/proxypanel/sync_users.php
```

---

### **Шаг 6: Проверка работы панели**

1. Перейдите на `http://ваш_IP/index.php`.
2. Логин: `admin`, Пароль: `admin`.
3. Создайте пользователя через `add_user.php`.
4. Убедитесь, что пользователь появился в `/etc/3proxy/passwd`.
5. Проверка API:

```
GET:  http://127.0.0.1/proxypanel/api_create_user.php?key=API_KEY&username=test&password=pass&ttl=60
POST: curl -X POST http://127.0.0.1/proxypanel/api_create_user.php -d "key=API_KEY" -d "username=test" -d "password=pass" -d "ttl=60"
```

---

### **Шаг 7: Рекомендации**

* Используйте HTTPS для безопасного доступа к панели.
* Ограничьте доступ к API по IP.
* Проверьте права на `/etc/3proxy/passwd` и перезапуск 3proxy.

---




