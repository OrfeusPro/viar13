# APP-005A-FORM-02 — Полнофункциональный parity-first перенос четырёхшагового quiz главной страницы

```text
TASK_ID: APP-005A-FORM-02
STATUS: DONE
PRIORITY: Critical
OWNER: программист
BRANCH: codex/app-005a-frontend-foundation
CREATED_AT: 2026-07-27
UPDATED_AT: 2026-07-27
DEPENDS_ON: APP-005A-FORM-01=DONE
PARENT: APP-005A
NEXT_ACTION: задача закрыта; продолжение frontend — APP-005A-ROUTE-01
```

## Цель

Перенести существующий четырёхшаговый `#q__form` без редизайна и с тем же
пользовательским поведением, но на безопасные Laravel 13 writers, isolated DB,
idempotency receipts и независимые outbound gates.

Метод выполнения: сначала переносится полный legacy-функционал и наблюдаемый
результат как в текущем production/local legacy. Адаптация под Laravel 13,
улучшение внутренней архитектуры, UX-коррекции и упрощение старых решений
разрешены только после подтверждённого parity baseline.

Главный критерий: каждое target-решение сравнивается с оригиналом —
legacy Blade/JS, `UserManageController::send_photo_form`,
`UserFormHelper::send_photo_form_helper`, mail templates, created `orders`
payload и browser behavior. Если target-логика отличается, отличие должно быть
явно названо как безопасная замена legacy-риска или задача остаётся в VERIFY.

## Обязательный первый этап

- `POST /send_photo_form` и `POST /user/send_photo_form` оба ведут в
  `UserManageController::send_photo_form`.
- Видимый legacy JS `script.js` перехватывает submit `.kviz`, делает
  `preventDefault()` и переводит форму на `data-step=5`; прямого browser POST
  из frozen `script.js` нет.
- Backend-контракт при прямом POST: нормализует phone, валидирует email/phone,
  honeypot `website`, timing `form_ts`, optional comment и file-field
  mismatch (`image` валидируется, `images` используется helper'ом).
- `UserFormHelper::send_photo_form_helper` берёт admin email из `GlobConfig`,
  пишет один файл `images` в `public/uploads/N-{Orders::getNextId()}.{ext}`,
  собирает `Email`, `Phone`, `Событие`, `Для кого`, `Сумма`, `Стиль`,
  `Расчет`, `Image`.
- Side effects legacy backend: `order_action`, создание нового `users` с
  plaintext-password mail `SendUserRegister`, direct `orders` insert со
  `status=watching`, `payment_status=not_payed`, `is_admin_order=2`, admin mail
  subject `Расчет портрета`, client mail `QuizSendToUser`, redirect `thanks`.

## Реализация 2026-07-27

- В target публичные routes зарегистрированы через единый
  `registerPublicFrontendRoutes`: один и тот же набор создаётся для RU без
  префикса и для группы `/{locale}`. В эту группу входят страницы, auth/reset
  endpoints и текущие writers `/all_styles_form`, `/send_photo_form`,
  `/user/send_photo_form`; новый frontend endpoint нельзя добавлять только в
  один языковой вариант.
- `#q__form` больше не имеет `data-endpoint-ready="0"`; visual/frozen step
  mechanics остаются в legacy `script.js`.
- `emoj_form.js` теперь отправляет AJAX с теми же quiz fields, первым выбранным
  файлом `images`, generated UUID idempotency key и локалью.
- Добавлены `HomepageQuizRequest`, `HomepageQuizService`, `HomepageQuizMail`.
  Writer создаёт совместимого user при необходимости, `orders` со
  `watching/not_payed/is_admin_order=2`, `public_form_submissions` receipt,
  безопасно хранит файл в `storage/app/public/quiz/{order}/original`, отправляет
  admin/client mail через sink/SMTP gate.
- Известное осознанное отличие от legacy: небезопасное письмо новому
  пользователю с plaintext password не воспроизводится. Это считается
  security replacement, а не functional redesign; при необходимости будет
  подключён safe account-setup flow отдельным gate по аналогии с FORM-01.
- Local-only `.env` включает `PUBLIC_HOMEPAGE_QUIZ_WRITES_ENABLED=true` и
  `PUBLIC_HOMEPAGE_QUIZ_OUTBOUND_ENABLED=false`; production/server не менялись.

## Evidence

- После ручной проверки обнаружен target gap: локализованная страница `/lt`
  отправляла quiz на `/lt/user/send_photo_form`, а target покрывал только
  `/send_photo_form` и `/user/send_photo_form`. Дополнительно старый
  `.kviz` submit-handler мог показывать legacy alert `Jūs nieko
  nepasirinkote!` параллельно с новым AJAX adapter.
- Исправление сначала добавило locale aliases, затем после архитектурной
  сверки с исходной `LaravelLocalization::setLocale()`-группой они заменены
  общей зеркальной регистрацией всего public frontend. Все локали ограничены
  `legacy_frontend.public_locales`, `SetPublicLocale` применяется на уровне
  группы, а формы строят action через `public_url()`. `emoj_form.js` снимает
  старый submit-handler только с `#q__form` и ставит scoped parity-submit.
- Regression: `HomepageQuizTest` теперь покрывает `/lt/user/send_photo_form`
  и locale `lt`; `HomepagePhotoLeadTest` покрывает `/en/all_styles_form`;
  homepage regression проверяет локализованные action для login, registration,
  reset, нижней формы и quiz.
- `HomepageQuizTest`: 5 tests / 41 assertions PASS.
- Public routing/forms/auth targeted suite: 38 tests / 534 assertions PASS.
- Full OpenServer PHP 8.3 suite: 107 tests / 2 203 assertions PASS.
- `pint --dirty --test`, `node --check public/theme/viar/js/emoj_form.js` и
  `git diff --check` PASS.
- Ручная LT-заявка создала order `18238`, но receipt показал точную причину
  отсутствия писем: `mail_status/admin/client=suppressed`, потому что
  local-only `PUBLIC_HOMEPAGE_QUIZ_OUTBOUND_ENABLED=false`.
- Payload этой отправки подтвердил `locale=lt`, но также `dest=null`.
  Target ошибочно принимал такую неполную заявку после снятия общего legacy
  submit-handler. Scoped adapter теперь проверяет выбранные шаги, файл и
  messenger через локализованные `data-*` тексты; server contract требует
  `dest` и хотя бы один из `step0/step1`.
- Русская строка, видимая после клика, найдена в Blade:
  `Отправка заказа`. Она заменена существующим переводом
  `portrait_buy_form.loading`; для LT результат — `Užsakymo siuntimas`.
- Client mail переведён с временного plain wrapper на byte-identical legacy
  `mail/quiz_send_to_user.blade.php` (SHA-256
  `8F869771F828B2975F769911C462E6AD5E1C8B92D6CAE65BE26EAD8A8A5A7B22`);
  subject и весь `@lang`-контент рендерятся в locale заявки. Admin mail
  остаётся служебным legacy content.
- Local quiz SMTP gate включён после подтверждения работающего verified SMTP.
  Для существующей заявки `18238` выполнен один контролируемый retry без
  повторного order: locale=`lt`, admin=`sent`, client=`sent`,
  overall=`sent`.
- Актуальный regression: 108 tests / 2 211 assertions PASS; Pint,
  JavaScript syntax и `git diff --check` PASS.
- Следующая browser-проверка подтвердила, что предупреждение LT показывается
  ожидаемо только при отсутствии выбранного `dest`. После закрытия popup клик
  по E-mail даёт `checked=true` и
  `kviz-messege__tab_active`.
- Ещё одна hardcoded строка `Файлы загружены` заменена существующим
  `trans('portrait.form_files_loaded')`. После reload `/lt` DOM показывает
  `Failai įkelti`, loading=`Užsakymo siuntimas`, locale=`lt`.
- Full regression после исправления: 108 tests / 2 212 assertions PASS;
  targeted homepage/quiz 11 / 122 PASS, Pint и `git diff --check` PASS.
- Письмо LT order `18239` подтвердило новый gap: оболочка
  `QuizSendToUser` была литовской, но в неё передавался общий legacy data
  block с `Email/Phone` и русскими `Для кого/Сумма/Стиль/Расчет`.
- Сверка `UserManageController::send_photo_form` показала точный legacy
  recipient contract: независимо от `dest=E-mail|WhatsApp` сначала отправляется
  служебное письмо admin, затем подтверждение клиенту на обязательный email.
  `dest` только фиксирует предпочтительный канал отправки расчёта менеджером.
- Target теперь строит два разных блока: order/admin сохраняют совместимый
  legacy content, client получает локализованные labels из `mail.php`.
  Добавлены `Email/Phone/Event/For whom/Budget/Style/Contact method/Image`
  для `lv|lt|pl|ru|de|en|ee`; значения выбранных шагов сохраняются без
  преобразований. LT regression запрещает русские labels в client mail.
- Актуальный full suite: 108 tests / 2 212 assertions PASS; Pint и
  `git diff --check` PASS. Translation corpus baseline обновлён после
  контролируемого дополнения существующих locale-файлов.
- Владелец повторил реальную LT-отправку после исправления и подтвердил
  результат словами «отлично, работает». Ручной mail/browser gate закрыт;
  task переведён из `VERIFY` в `DONE`.

## Definition of Done

- UI и file/country/step behavior совпадают с оригиналом;
- в isolated DB создаётся тот же бизнес-результат без duplicate;
- MIME, размеры и совокупный объём файлов ограничены;
- клиентские и служебные письма локализованы и наблюдаемы отдельно;
- production Synvolve/CRM/SMTP не вызываются без собственного gate;
- success/error/redirect parity подтверждена в браузере;
- targeted и полный suite PASS, evidence и документация обновлены.

## Stop conditions

- обнаружен вызов production DB/storage/webhook;
- не прослежен хотя бы один вложенный side effect;
- предлагается редизайн или переписывание frozen assets до parity;
- для продолжения нужен новый production credential.
