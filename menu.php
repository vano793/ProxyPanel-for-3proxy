<?
/*
Proxy Panel — скрипт управления 3proxy пользователями
Разработка кода: ChatGPT (OpenAI)
Концепция и видение: Иван Михайлович
Лицензия: GNU General Public License v3.0 (GPL-3.0)
Ссылка на лицензию: https://www.gnu.org/licenses/gpl-3.0.html
*/?>
<?php
// menu.php
?>
<!-- Меню навигации -->
<nav class="sb-nav" aria-label="Главная навигация">
  <!-- Левая часть меню -->
  <div class="sb-nav-left">
    <a href="dashboard.php">Статистика</a>

    <!-- Шторка "Пользователи" -->
    <div class="sb-dropdown" id="sb-users-dropdown">
      <button
        class="sb-dropdown-toggle"
        aria-haspopup="true"
        aria-expanded="false"
        aria-controls="sb-users-menu"
        id="sb-users-toggle"
        type="button"
      >
        Пользователи
        <span class="sb-caret" aria-hidden="true">▾</span>
      </button>

      <div class="sb-dropdown-menu" id="sb-users-menu" role="menu" aria-labelledby="sb-users-toggle">
        <a href="add_user.php" role="menuitem">Добавить пользователя</a>
        <a href="users.php" role="menuitem">Список пользователей</a>
      </div>
    </div>

    <a href="sync_users.php">Синхронизация</a>
    <a href="test_write.php">Тест записи</a>
    <a href="api_instructions.php">Инструкция API</a>
  </div>

  <!-- Правая часть меню -->
  <div class="sb-nav-right">
    <a href="logout.php">Выход</a>
  </div>
</nav>

<style>
/* === Переменные для стилей меню === */
:root{
  --sb-nav-bg: #f7f7f7;
  --sb-nav-border: #e2e2e2;
  --sb-nav-link-color: #333333;
  --sb-nav-link-hover-bg: #e9e9e9;
  --sb-nav-accent: #444444;
  --sb-dropdown-bg: #ffffff;
  --sb-dropdown-border: #dedede;
  --sb-font-family: "Helvetica Neue", Arial, sans-serif;
  --sb-padding: 10px;
  --sb-radius: 8px;
  --sb-shadow: 0 6px 18px rgba(0,0,0,0.06);
  --sb-transition: 180ms ease;
  --sb-breakpoint-mobile: 700px;
}

/* === Сброс минимума === */
*{box-sizing:border-box}
body{margin:0;font-family:var(--sb-font-family);color: var(--sb-nav-link-color);}

/* === Контейнер навигации === */
.sb-nav {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  background: var(--sb-nav-bg);
  border-bottom: 1px solid var(--sb-nav-border);
  padding: 8px;
}

/* Левая и правая часть меню */
.sb-nav-left {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.sb-nav-right {
  margin-left: auto;
}

/* Ссылки и кнопки */
.sb-nav a,
.sb-nav button.sb-dropdown-toggle {
  display:block;
  padding: 8px 12px;
  color: var(--sb-nav-link-color);
  text-decoration: none;
  border-radius: 6px;
  font-size: 15px;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background var(--sb-transition), color var(--sb-transition);
}
.sb-nav a:focus,
.sb-nav button.sb-dropdown-toggle:focus {
  outline: 3px solid rgba(0,123,255,0.18);
  outline-offset: 2px;
}
.sb-nav a:hover,
.sb-nav button.sb-dropdown-toggle:hover {
  background: var(--sb-nav-link-hover-bg);
}

/* === Dropdown === */
.sb-dropdown {position: relative;}
.sb-dropdown-toggle {
  display:flex;
  align-items:center;
  gap:8px;
  font-weight:600;
}
.sb-dropdown-toggle .sb-caret {
  font-size: 12px;
  transform: rotate(0deg);
  transition: transform var(--sb-transition);
}
.sb-dropdown-menu {
  display: none;
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  min-width: 200px;
  background: var(--sb-dropdown-bg);
  border: 1px solid var(--sb-dropdown-border);
  border-radius: var(--sb-radius);
  box-shadow: var(--sb-shadow);
  padding: 6px;
  z-index: 1200;
}
.sb-dropdown-menu a {
  display:block;
  padding: 8px 10px;
  margin:2px 0;
  border-radius:6px;
  text-decoration:none;
  color:var(--sb-nav-link-color);
  font-weight:500;
}
.sb-dropdown-menu a:hover,
.sb-dropdown-menu a:focus {background: var(--sb-nav-link-hover-bg);}

/* Открытое состояние */
.sb-dropdown.open > .sb-dropdown-toggle .sb-caret {transform: rotate(180deg);}
.sb-dropdown.open > .sb-dropdown-menu {display:block;animation: sb-dropdown-show var(--sb-transition) ease;}
@keyframes sb-dropdown-show {from {opacity:0; transform: translateY(-6px);} to {opacity:1; transform: translateY(0);}}

/* Адаптив: мобильные */
@media (max-width: var(--sb-breakpoint-mobile)) {
  .sb-nav {gap:6px;padding:8px;}
  .sb-dropdown {width:100%;}
  .sb-dropdown-menu {position: static;margin-top:6px;}
}
</style>

<script>
// === JS для шторки ===
(function(){
  const dropdown = document.getElementById('sb-users-dropdown');
  const toggle = document.getElementById('sb-users-toggle');
  const menu = document.getElementById('sb-users-menu');

  toggle.addEventListener('click', function(e){
    const isOpen = dropdown.classList.toggle('open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  document.addEventListener('click', function(e){
    if (!dropdown.contains(e.target)) {
      if (dropdown.classList.contains('open')) {
        dropdown.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    }
  });

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') {
      if (dropdown.classList.contains('open')) {
        dropdown.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
      }
    }
  });

  menu.querySelectorAll('a').forEach(a=>{
    a.addEventListener('click', ()=>{
      dropdown.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
})();
</script>
