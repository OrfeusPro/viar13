# APP-005A-FORM-01 — Полнофункциональный перенос формы главной страницы «Загрузить фото»: валидация, безопасная загрузка файлов, создание заявки, защита от повторной отправки, письма через локальный sink и автоматические тесты

```text
TASK_ID: APP-005A-FORM-01
STATUS: DONE
PRIORITY: Critical
OWNER: программист
BRANCH: codex/app-005a-frontend-foundation
CREATED_AT: 2026-07-22
UPDATED_AT: 2026-07-27
DEPENDS_ON: APP-005A homepage parity; S0-03 auth/session
PARENT: APP-005A
NEXT_ACTION: задача закрыта; продолжение frontend — APP-005A-FORM-02
```

## Цель

Сделать форму главной «Загрузить фото» реально работающей в Laravel 13 с
сохранением пользовательского payload и бизнес-результата legacy-сценария, но
с безопасной загрузкой, idempotency и изолированными mail side effects.

## Входит в задачу

- существующая нижняя форма `.why-form` «Отправьте фото» и POST `/all_styles_form`;
- email, телефон, `new_name` и массив `file[]`;
- создание/повторное использование пользователя и создание lead-order;
- receipt/idempotency и ссылки на сохранённые файлы заказа;
- письма администратору и клиенту через управляемый mail transport;
- JSON success/validation/duplicate contract для текущего AJAX UI;
- feature-тесты и browser smoke без production/server изменений.

## Полная исходная цепочка, обязательная для parity

1. `UserManageController::send_all_styles_form` валидирует форму и сохраняет
   временные файлы через `UserFormHelper::send_photo_portrait_form_helper`.
2. `Admin\OrdersController::create_admin_order` находит или создаёт пользователя.
3. Для нового пользователя legacy отправляет `SendUserRegister`; перенос
   plaintext-пароля запрещён, поэтому требуется безопасный password-setup
   compatibility contract.
4. `create_admin_order_action` создаёт заказ, переименовывает файлы и отправляет
   клиенту локализованное `SendAdminOrder` на шаблоне
   `mail/your_order_given.blade.php`.
5. После этого вызывается
   `SynvolveWebhookService::notifyOrderSnapshotById(..., 'admin_order_created')`.
6. Управление возвращается в `send_all_styles_form`, который отдельно отправляет
   служебное письмо администратору: тема `Быстрый заказ №...`, тело `Email`,
   `Phone` и ссылки на файлы.
7. Успешный сценарий завершает локализованный redirect
   `/thanks?order_id=...`.

Target нельзя считать эквивалентным, если проверены только order/file/redirect:
оба письма, язык, новый/существующий пользователь и disposition webhook должны
иметь отдельное evidence.

## Не входит

- четырёхшаговый quiz `#q__form`, popup quick order, cart/email и checkout;
- Socialite и production-активация provider/webhook credentials;
- изменение frozen CSS/JS bundles или редизайн формы;
- production/test deployment.

## Предварительные условия

- [x] target работает на isolated local DB;
- [x] mail transport и outbound gate настраиваются отдельно от production;
- [x] production write-owner остаётся legacy;
- [x] legacy request/DB/file/mail contract зафиксирован без PII.

## План выполнения

- [x] снять legacy contract и DB schema;
- [x] добавить task-specific writer gate и idempotency receipt;
- [x] реализовать validation, transaction, user/order writer и safe file storage;
- [x] подключить существующий AJAX UI и success/error states;
- [x] сохранить исходную legacy-индикацию выбранных файлов без отдельного preview/redesign;
- [x] проверить success/validation/duplicate/disabled/failure cleanup;
- [x] обновить evidence и контрольную документацию.

## Definition of Done

- [x] валидная форма создаёт ровно одну заявку и сохраняет допустимый файл;
- [x] повтор с тем же idempotency key не создаёт второй заказ/файл/письмо;
- [x] invalid MIME/size/contact/honeypot rejected без DB/file delta;
- [x] writer gate false исключает state changes;
- [x] при разрешённом outbound письма реально доставляются через SMTP, секреты и персональные данные не логируются дополнительно;
- [x] после успешной отправки выполняется переход на локализованную страницу «Спасибо»; validation/network errors остаются видимыми в форме;
- [x] production/server не изменены;
- [x] tests/evidence/docs/commit приложены.

## Evidence

| Дата | Evidence | Результат |
|---|---|---|
| 2026-07-22 | legacy source: `UserManageController::send_all_styles_form`, `UserFormHelper::send_photo_portrait_form_helper`, `why7_form.blade.php` | выбран правильный visible form contract |
| 2026-07-22 | target commit `cf03ec9` | writer, validation, UUID storage, receipt/idempotency, mail sink и отдельный JS adapter реализованы |
| 2026-07-22 | targeted suite | 11 tests / 103 assertions PASS |
| 2026-07-22 | full suite, OpenServer PHP 8.3 + `ext-intl` | 92 tests / 2 082 assertions PASS |
| 2026-07-22 | local HTTPS smoke | order `18221` создан, receipt=`completed`, mail=`sent_to_sink`, outbound=`blocked_by_gate`; затем user/order/receipt/file удалены, residual counts=0 |
| 2026-07-22 | Chrome smoke | форма найдена и заполнена; attach/success-popup требует разрешения расширения `Allow access to file URLs` |
| 2026-07-22 | target commits `a31f9bd`, `c976c63` | preview/remove UI, client file limits, runtime-upload baseline exception и evidence добавлены без изменения frozen bundles |
| 2026-07-22 | targeted/full suites | 15 tests / 145 assertions и 96 tests / 2 124 assertions PASS; Pint, Node syntax, HTTPS assets и diff-check PASS |
| 2026-07-27 | live differential `viarcanvas.loc/en` ↔ `viar-filament.loc/en` | выявлено, что preview-adapter не соответствует оригиналу и блокирует применение выбранной страны; extra preview/CSS удалены, legacy status восстановлен, смена Latvia → Germany в target PASS |
| 2026-07-27 | geometry/browser evidence | обе формы при `2560 × 1249` имеют одинаковые координаты и размеры form/title/group/phone/submit; отдельной preview-карточки в target больше нет |
| 2026-07-27 | regression | targeted 20 tests / 421 assertions; full 97 / 2 128 PASS; Pint, JS syntax и diff-check PASS |
| 2026-07-27 | target commit `09cb1bf` | точное legacy-поведение формы восстановлено и закреплено тестами/evidence |
| 2026-07-27 | локальная отправка заказа `18233`, Laravel log | заказ и файл созданы; выявлено расхождение post-submit contract: письма попали в `log`, JSON adapter пытался открыть отсутствующий `.thanks`, перехода на `/thanks` не было |
| 2026-07-27 | target commit `a1350b7` | добавлены локализованные `/thanks`, единый JSON/HTTP redirect contract, явный locale, отдельный form outbound gate и SMTP states |
| 2026-07-27 | automated/local HTTPS evidence | targeted 13 / 95, full 100 / 2 137, Pint/JS/diff PASS; `/thanks?order_id=18233` = 200; SMTP TCP connect=true |
| 2026-07-27 | target commit `7ca2856`, SMTP retry `18234` | после отключения Avast verified SMTP auth PASS; admin/client messages приняты SMTP; receipt=`mail_status:sent/outbound_status:sent`; `MAIL_VERIFY_PEER` default=true |
| 2026-07-27 | legacy deep call-graph (`create_admin_order_action`) | выявлено, что упрощённый target пропустил реальный `SendAdminOrder`: двухстрочная русская заглушка не является оригинальным клиентским письмом |
| 2026-07-27 | target local parity rebase | побайтно перенесены `your_order_given` и 4 вложенных mail Blade; `/en` client render: subject=`Viarcanvas admin order`, English=true, Russian=false, 82 663 bytes; corrected client mail `18235` принят SMTP |
| 2026-07-27 | target commit `d324ae2` | исходный локализованный client mail corpus и отдельное legacy-compatible admin письмо перенесены; заказ `18235` повторно отправлен только клиенту |
| 2026-07-27 | target commit `de45c62` | plaintext-пароль заменён локализованной expiring password-setup ссылкой; исходный `admin_order_created` payload перенесён за отдельным default-false gate; добавлены раздельные delivery statuses |
| 2026-07-27 | delivery observability и regression | admin/client/account/Synvolve получили отдельные статусы; targeted 15 / 108, full OpenServer PHP 8.3 102 / 2 150 PASS |
| 2026-07-27 | ручная приёмка владельцем | исправленное локализованное письмо и сценарий нового пользователя подтверждены словами «все отлично»; FORM-01 принят и закрыт |

## Журнал работы

| Дата | Что сделано | Блокер | Следующее действие |
|---|---|---|---|
| 2026-07-22 | после сверки двух homepage forms scope уточнён: FORM-01 — нижняя `.why-form` с `file[]`; quiz остаётся следующим slice | нет | снять точный DB/file/mail contract |
| 2026-07-22 | legacy contract снят; реализация и локальная миграция выполнены; HTTPS success/cleanup и полный suite PASS | Chrome extension не разрешает local file upload | включить file URL access и подтвердить видимый success-popup; production enablement отдельно требует provider/Synvolve adapter и staging gate |
| 2026-07-22 | пользовательская проверка выявила отсутствие миниатюр выбранных изображений; legacy handler для `.why-form` выводит только общий статус и не имеет `.images-container` | UI parity/feedback gap | реализовать scoped preview/remove adapter без изменения frozen bundles |
| 2026-07-22 | добавлены thumbnails/fallback cards, имя/размер, удаление одного/всех файлов и client count/size checks; server и full regression PASS | Chrome extension запрещает автоматический `fileChooser.setFiles` | пользователь вручную подтверждает select/remove и success-popup; после PASS закрыть FORM-01 |
| 2026-07-27 | выяснено, что заказ 18232 проверялся в legacy `viarcanvas.loc`: временный `/uploads/1785136561_1.png` был штатно перенесён в `public/orders/N18232-...png`; target-only receipt migration повторно применена после refresh БД | OpenServer HTTPS сейчас не слушает | запустить OpenServer и отправить форму именно на `https://viar-filament.loc/`; проверить `/storage/orders/{id}/original/...` и receipt |
| 2026-07-27 | после прямого сравнения с оригиналом ошибочный preview/remove слой удалён; submit adapter оставлен только для Laravel 13 JSON endpoint, legacy `custom.js` снова единолично управляет file UI; выбор страны снова применяется | расширение Chrome не разрешает автоматический `setFiles` | пользователь вручную прикрепляет фото и подтверждает «Files uploaded» без новых карточек, затем проверяется success-popup |
| 2026-07-27 | ручная проверка target подтвердила создание заказа `18233`, но не подтвердила письмо и страницу «Спасибо»; по коду найдено, что mail transport=`log`, а success adapter вызывает отсутствующий popup | post-submit contract реализован не как в legacy | добавить `/thanks`, возвращать `redirect_url`, выполнять browser redirect и включить SMTP только в локальном target `.env` |
| 2026-07-27 | post-submit contract исправлен в `a1350b7`: общий Stage 0 outbound остаётся false, локальный SMTP разрешён только через `PUBLIC_HOMEPAGE_PHOTO_LEAD_OUTBOUND_ENABLED`; страница и redirect проверены | реальная доставка не проверяется автотестом без внешнего письма | пользователь отправляет одну новую заявку и подтверждает страницу «Спасибо» и входящее письмо; затем проверить receipt и закрыть VERIFY |
| 2026-07-27 | заявка `18234` перешла на `/en/thanks`, но письмо не пришло; receipt=`mail_status:failed/outbound_status:failed`; SMTP start выявил `certificate verify failed` | Avast Mail Shield выдаёт локально недоверенный сертификат; legacy Laravel 6 работал с `verify_peer=false` | ввести env-controlled compatibility с безопасным default=true, локально отключить проверку, затем повторить письма заказа один раз |
| 2026-07-27 | пользователь отключил Avast; SMTP снова отдаёт certificate `mail.viarcanvas.com`, Symfony verified auth прошёл; оба штатных письма `18234` повторены один раз и сервером приняты | ожидается подтверждение конечного mailbox | проверить входящие/спам; после подтверждения поставить FORM-01=`DONE` |
| 2026-07-27 | пользователь уточнил, что пришло только русское служебное письмо, а клиентское отсутствовало; deep audit обнаружил скрытый внутри `create_admin_order_action` локализованный `SendAdminOrder` | target client mail был самостоятельно придуманной русской заглушкой | перенести исходный mail corpus, связать с locale формы, затем отдельно закрыть registration/Synvolve boundaries |
| 2026-07-27 | parity-first цепочка реализована полностью: два разных письма, безопасный account setup, исходный Synvolve snapshot contract и раздельные статусы; production Synvolve URL локально защищён отдельным false gate | требуется ручное подтверждение конечного mailbox, автоматический suite PASS | подтвердить письмо `18235`; отправить форму с новым email и проверить client + password-setup mail, после чего закрыть FORM-01 |
| 2026-07-27 | пользователь подтвердил успешную ручную проверку писем и формы; все пункты DoD закрыты | нет | FORM-01=`DONE`; следующая отдельная задача — FORM-02 для четырёхшагового quiz |

## Stop conditions

- writer обращается к production DB/storage/SMTP;
- legacy обязательный бизнес-инвариант нельзя определить по коду и локальной схеме;
- нужен новый provider credential или необратимое изменение существующих данных;
- валидация допускает executable/public upload либо duplicate order creation.
