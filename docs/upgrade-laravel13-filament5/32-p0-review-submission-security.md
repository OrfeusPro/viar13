# 32. План P0 security: отправка и публикация отзывов

Дата аудита и актуализации: **14.07.2026**.

> Статус: **план, не реализация**. Application code, routes, Blade, config и tests возвращены в исходное состояние. Ни один production deployment не выполнялся.

## 1. Подтверждённые потоки

| Поток | Route и форма | Хранилище | Подтверждённые проблемы текущего кода |
|---|---|---|---|
| current | GET/POST `/review`; `file[1]`, `file[2]`, base64 `audioData` | `reviews`, `storage/app/public/review` | FormRequest импортирован, но не используется; guest email может связать отзыв с user/completed order; нет server limits; `$audioDB` может быть undefined |
| legacy | GET `/user/send_review`; POST `/user_send_rev/{locale}`; `avatar_photo`, `promo_foto`, `audio_file` | `our_works`, `storage/app/public/uploads` | POST без auth/rate; нет server limits; locale читается из `locace`; synchronous mail; client filename сохраняется |

`reviews` и `our_works` — разные действующие contracts. Их нельзя объединять без решения owner: формы, поля, moderation, publication и notification behavior расходятся.

## 2. Безопасные агрегаты локальной БД

Содержимое отзывов, email, имена и пути файлов не читались.

- `reviews`: 4 rows; active 0; image 4, avatar 3, audio metadata 4; locales `ee=1`, `ru=3`.
- `our_works`: 17 rows; active 15; image/avatar/audio metadata по 15; locales `NULL=1`, `ee=2`, `en=1`, `lt=1`, `lv=2`, `ru=10`.

Это подтверждает реальный legacy corpus и необходимость сохранить данные и locale/null anomaly до отдельной reconciliation.

## 3. Подтверждённые риски

- guest может подставить email существующего user и получить неверную связь review → completed order;
- файлы и base64 audio принимаются без достаточных MIME/size/structure limits;
- media сразу попадает на public disk до moderation;
- raw review text выводится через `{!! !!}` в шести Blade consumers, что создаёт stored-XSS риск;
- нет idempotency/duplicate window, quarantine/AV/CDR, retention/DSAR и orphan reconciliation;
- legacy synchronous mail может превратить сохранённый review в видимую ошибку и спровоцировать повтор;
- validation UX и сообщения не согласованы для `lv|lt|pl|ru|de|en|ee`.

## 4. Рекомендуемый P0 contract

### Identity и ownership

1. Оба POST требуют auth и разумный throttle.
2. Submitted email должен совпадать с authenticated user; в DB записывается server-side email.
3. Current review связывается только с latest completed order этого user; `pid` извлекается только из owned order.
4. Если guest review является бизнес-требованием, нужен signed/tokenized order context, а не lookup по email.

### Поля и файлы

1. Сохранить фактический current contract `file[1]`/`file[2]`; неизвестные slots отклонять.
2. Ввести limits для name/text, count, MIME и decoded size.
3. Images: content MIME allowlist, pixel/dimension limits, metadata stripping; SVG запретить.
4. Base64 audio: strict data URI, Base64, WebM structure/magic и decoded limit.
5. Генерировать server filenames; client original filename не хранить как trusted path/name.
6. Сохранять media в private quarantine, затем promotion/delete по moderation decision.

### Atomicity, mail и XSS

1. При DB failure удалить только созданные этим request files; добавить failpoint tests.
2. Notification вынести после commit в idempotent outbox/queue; ошибка mail не должна создавать повторный review.
3. Нормализовать new review text как plain text или применить строгую sanitizer policy.
4. Заменить raw output review text на escaped output во всех шести consumers; перед публикацией проверить существующий corpus.

## 5. Обязательные тесты реализации

- guest deny либо valid signed guest context;
- spoofed email/foreign-order отрицательный сценарий;
- корректная связь с owned latest completed order;
- invalid locale, slot 3, SVG/spoof MIME, oversize image/audio, malformed WebM/Base64;
- throttle и duplicate/replay window;
- DB failpoint без orphan files;
- mail failure после сохранения без duplicate insert;
- escaped display существующего и нового текста;
- seven-locale browser/mobile flow и translated inline errors;
- quarantine → moderation → promotion/delete lifecycle.

Результаты временного прототипа не являются зелёным baseline текущего проекта. После реализации весь corpus запускается заново на PHP 7.4 и затем на каждом шаге framework migration.

## 6. Решения до начала разработки

- `reviews` и `our_works`: `merge`, `retain` или `archive`;
- допускаются ли guest reviews и какой ownership proof используется;
- MIME/size/dimension limits и обязательность AV/CDR;
- moderation owner, private preview и promotion/delete SLA;
- retention/DSAR для email, voice, avatar и order link;
- политика дубликатов и уведомлений;
- сохранение всех public locales и переводов validation UI.

## 7. Production acceptance

Нужны backup/config fingerprint, PHP/proxy upload limits, filesystem permissions, authenticated/signed seven-locale browser tests, negative security probes, zero foreign-order links, отсутствие orphan media и production-safe logging без PII. Rollback возвращает предыдущий artifact, но новые review IDs/files reconciled отдельно; существующие orders и отзывы не откатываются поверх live данных.
