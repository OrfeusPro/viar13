# ADM-AUD-04A — Email Рассылка

## Реализовано

- Filament `/filament/email-sender`: поиск пользователей по email/имени,
  выбор типов/языков, тема/приветствие/HTML/прощание, превью фактического письма,
  число получателей, подтверждение и результат. `?id=` заранее выбирает
  пользователя, неизвестный ID даёт 404.
- Старый пункт `/admin/email-sender` сопоставляется с новой страницей в
  существующем дереве меню. Порядок и данные menu_items не изменяются.
- Пустые фильтры означают всех; списки пересекаются (AND), внутри списка OR.
  Источники: users, user_types, locales, settings.locale. Допуск: news=YES и
  корректный email. Подписка/адрес перепроверяются перед постановкой, а также
  worker через shouldSend; письмо заказа сохраняет прежнее правило допуска.
- Права: существующие browse_admin + browse_users + edit_users; проверяются
  на странице, поиске и серверных действиях без кеширования разрешений.
  Новых permissions и изменений пользовательских ролей нет.
- Превью изолировано iframe sandbox без скриптов/доступа к странице и CSP,
  запрещающей внешние загрузки. Оно использует AdminMailNotification::toMail,
  но не вызывает доставку. Внешние картинки в превью не загружаются.
- AdminMailNotification выводит прощание; первая UserMessage читается явно,
  uns_text переводится по preferredLocale с fallback ru. Отсутствующий текст
  даёт понятную validation error. Ссылка отписки сохранена совместимой.

## Схема и защита повторов

Миграция `2026_10_08_230000_create_admin_email_campaigns_table.php` создаёт
только новую служебную таблицу `admin_email_campaigns`: уникальный token,
user_id владельца, status, payload, recipients snapshot, result, timestamps.
Существующие users/локали/подписки/изображения не мигрируются.
Rollback удаляет только эту таблицу; проверено на SQLite.

Подготовка сохраняет серверный snapshot письма и аудитории. Подтверждение
принимает только token своего пользователя; данные публичной формы не
используются как новое письмо после подтверждения. Locked свойства Livewire
защищают token/превью/результат. Изменение формы сбрасывает подтверждение.
Превью действует 30 минут, аудитория ограничена 1000 адресатами за кампанию.

Состояния: prepared → processing → queued/suppressed/error/uncertain.
Claim выполняется транзакционно с lockForUpdate и фиксируется **до** queue
calls. Повтор prepared разрешён только до claim; остальные состояния возвращают
сохранённый результат и duplicate. При остановке процесса processing остаётся
неопределённым и автоматически не переигрывается. При исключении queue call
зафиксированы queued/uncertain/not_attempted; цикл останавливается.
Не заявляется exactly-once SMTP доставка — это отдельный внешний транспорт.
Перед повторной кампанией после uncertain нужно сверить очередь, чтобы не
дублировать уже принятые сообщения.

## Режим отправки

`admin_migration.bulk_email_enabled` / `ADMIN_BULK_EMAIL_ENABLED`, default false.
При false подтверждение записывает suppressed и не обращается к Notification
dispatcher. Флаг письма одного заказа и .env не изменялись.
При true разрешены асинхронные database/redis/sqs/beanstalkd драйверы;
sync/null/неизвестный драйвер отклоняется до claim. Queued означает передачу
Notification в очередь, не доставку SMTP. Worker повторно проверяет bulk flag,
подписку и совпадение адреса с snapshot. Включение и worker в этом этапе не
выполнялись.

## Проверки и оставшиеся действия

- EmailCampaignTest, OrderRecipientEmailServiceTest, BreadMenuNavigationTest:
  **19 tests / 123 assertions PASS**, 0 skipped.
- SQLite :memory:, Notification/Mail fake, HTTP preventStrayRequests.
  Проверены пересечение фильтров, подписка/невалидный адрес, неизвестные
  фильтры, preview/HTML/прощание/перевод отписки, disabled, duplicate,
  асинхронная fake постановка, отзыв доступа, чужой token, ?id, устаревшее
  превью, interrupted claim, частичный сбой, изменение адреса/подписки,
  worker guard, предел аудитории и rollback без изменения users.
- PHP lint восьми файлов, git diff --check PASS. Превью и Blade страницы
  отрендерены в Livewire-тесте. Реальные SMTP/HTTP/worker и рабочие записи
  не использовались. Внешняя доставка и MySQL concurrency не подтверждены.
- После изолированных проверок применена только новая миграция к локальной
  MySQL `localhost / viar_laravel13` (проверены runtime connection и SELECT
  DATABASE). Существование browse_admin/browse_users/edit_users подтверждено
  чтением permissions; права не менялись. Создана служебная таблица, записей
  рассылок/писем на рабочих пользователях не создавалось. Bulk flag false.
- Chrome read-only: страница открывается, все поля и режим без отправки видны,
  пункт меню сохраняет место и активную подсветку, отступы/форма проверены по
  screenshot. Prepare/Confirm в Chrome не нажимались. Вкладка оставлена открытой.

ADM-AUD-04A и перенос ADM-AUD-04 — DONE в согласованной области локальной
реализации и проверок с подменой отправок. Реальная доставка/production worker,
MySQL concurrency и мобильная визуальная приёмка остаются отложенными.
Следующая задача: ADM-AUD-05 — список клиентов.
