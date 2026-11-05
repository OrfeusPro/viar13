# Восстановление входа на dev.viarcanvas.com

Диагностика 2026-10-23: GET /filament/login — 200; указанный в HTML
Livewire JS `/livewire-1fe4a532/livewire.js?id=4d123128` — 404.
Остальные восемь подключённых внешних JS — 200. В HTML есть Rocket Loader.
Без Livewire/Alpine вход не отправляется, а исходный revealable password input
не имеет нативного type и отображается как text. Пароли не вводились.
Перехват динамического .js статическим location Nginx — вероятная причина,
конфигурация сервера не проверена.

## Применение на сервере

После загрузки изменений из этого репозитория выполнить из корня приложения
тем же PHP, которым работает сайт:

```sh
php artisan vendor:publish --tag=livewire:assets --force
php artisan filament:assets
php artisan optimize:clear
```

`public/vendor/livewire` должен быть доступен из document root (`public`).
Composer post-autoload-dump теперь публикует runtime автоматически; при
`--no-scripts` эти команды нужно выполнять явно. После каждого обновления
Livewire публикацию повторять: старые assets несовместимы с новым runtime.
Публикация не меняет БД и пароли. Не выполнять composer update для этой починки.

В Cloudflare создать Configuration Rule для hostname dev.viarcanvas.com и
path `/filament` или начинающегося с `/filament/`: Rocket Loader = Off.
Очистить Cloudflare cache страницы входа и Livewire assets, если они кешировались.
Правила Cache Everything должны исключать админку и динамические Livewire URL.

Публикация решает загрузку основного runtime через статический URL.
Для динамических Livewire component JS/CSS и preview/upload маршрутов также
нужен fallback в Laravel. Если Nginx перехватывает `.js`/`.css`, перед regex
location статики добавить в соответствующий server block (проверив существующий
PHP front controller):

```nginx
location ^~ /livewire- {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Проверить `nginx -t` перед reload. Конфигурация здесь приведена как шаблон,
автоматически на сервер не применялась.

## Приёмка

1. Открыть страницу с полной перезагрузкой: пароль скрыт сразу, кнопка показа работает.
2. В Network основной `/vendor/livewire/livewire*.js?id=...` возвращает 200 и JavaScript.
3. Войти существующей учётной записью: запрос Livewire update проходит, открывается панель.
4. Если запрос уже отправляется, но вход отклонён, проверить конкретный ответ:
   419 — cookies/session/HTTPS/domain; неверные credentials — БД/хеш пароля;
   403 — наличие `browse_admin`. Не выдавать новые права без проверки причины.

Успешный онлайн-вход и настройки сервера пока не проверены; требуется применение
на хостинге. Локальный feature test проверяет нативный type=password и сохранение
Alpine toggle, существующие auth tests — разделение guards и права.
