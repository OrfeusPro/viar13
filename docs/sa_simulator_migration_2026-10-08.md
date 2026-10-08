# ADM-AUD-11 — SA Simulator в Filament

Дата: 2026-10-08. Перенос страницы и исполнения DONE; production SA UAT остаётся открытым.

## Перенесено

- `/filament/sa-simulator`, пункт меню «Дополнительно», ссылки из списка/карточки SA.
- Все 34 legacy presets: lead/create/pricing/coupon/bonus/GC/FC/HM exact; inbound/preorder/bot/media/status; pipeline/escalation/outbound; catalog/sizes/price/lookup; Blog API.
- JSON editor, метод GET/POST/PATCH, справка обязательных и опциональных полей, ошибки/коды, копирование cURL, response JSON и validation details.
- Defaults из существующего DB resolver AdminSaIntegrationController; бизнес-контроллеры/API не дублируются. API key только на сервере, в cURL YOUR_API_KEY. Неизвестные/абсолютные URL не исполняются.
- Повтор запроса сохраняет event_id/idempotency_key; выбор пресета генерирует новые значения. POST/PATCH требуют подтверждения. Это записи в текущую базу, не автоматический rollback.
- Доступ: browse_admin + browse/read/edit/add_orders. Blog writes дополнительно требуют add_blog_authors/add_blog_posts/edit_blog_posts. Права перепроверяются на каждом вызове.
- Исходящие CRM→SA закрыты при sa_commands_enabled=false; ADM-FIL-UAT-* никогда не исполняются через исходящие simulator endpoints даже при включённой отправке. Для них используется существующий UAT чат.
- Blog media, как в оригинале: multipart cURL, JSON-кнопка объясняет ограничение. Новая форма загрузки не добавлялась.

## Исполнение и проверка

Livewire executeSimulator передаёт endpoint/payload сервису с явной allowlist. Метод определяется на сервере; внутренний Request проходит VerifyIntegrationApiKey и существующий API handler. Нет произвольного HTTP/loopback, отключения TLS или передачи секретов в browser. Исходный request восстанавливается после выполнения. Логи содержат author/path/method/status без key/payload.

- SaSimulatorTest: **6 tests / 38 assertions PASS**. Реальный messagesWebhook на SQLite :memory: success/validation/duplicate; allowlist/JSON/disabled outbound/UAT guard/auth/GET routing/page permissions. HTTP/mail fakes; реальные записи не менялись.
- Simulator + Inbox + OrderSaCommand до последнего дополнительного UAT guard test: **52 tests / 452 assertions PASS**.
- PHP lint новых Page/Service PASS; Blade view:cache PASS.
- Chrome: GET services-catalog **200**, JSON response показан; переключение на message без заказа изменило endpoint/editor/docs. JS errors: 0. Screenshot storage/app/testing/sa-simulator-2026-10-08.png. Записывающие сценарии в текущей БД не запускались.

## Осталось по SA

Сквозной тест на согласованном тестовом контакте: ingress → reply → provider callback/delivery → bot, toast/beep и touch. Повторная проверка импортированных order links после базы прода. Перенос симулятора не подтверждает production transport и конкурентные блокировки MySQL.
