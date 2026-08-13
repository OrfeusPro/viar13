# FN-09: авторизация, Socialite, личный кабинет и четыре потока чатов

Дата среза: 14.07.2026. Решения владельца обновлены 17.07.2026. Статус: **статический L2 и read-only local DB aggregate завершены; owner direction получен; HTTP/provider/runtime L3 и production metadata открыты**.

## 1. Итог

Текущая реализация не может быть перенесена в Laravel 13 / Filament 5 простым копированием `auth` middleware. В проекте параллельно работают стандартные Laravel auth routes, собственные AJAX login/register/reset, Voyager login, Facebook/Google Socialite, старый и новый личные кабинеты и четыре legacy chat stream.

Положительно подтверждено:

- страницы заказов клиента выбирают только `orders.user_id = Auth::id()`;
- художник видит заказы через `painter_orders.user_id = Auth::id()`;
- повторная оплата использует отдельный `resolveClientOrderForPayment()`, проверяет владельца и запрещает painter;
- `ClientOrderPaymentTest` проходит: 3 теста, 7 assertions;
- page-render Blade в исследованных chat templates использует escaped `{{ ... }}`;
- CSRF действует для web POST routes, session payload шифруется.

Критические разрывы:

1. `POST /admin/check-user` публичен, выполняет substring/exact поиск и возвращает сырые строки `users`; Eloquent `$hidden` не применяется, поэтому контракт потенциально раскрывает password hash, remember token и PII.
2. Большинство account/chat/file/status mutations имеют только `auth`, но не проверяют роль и принадлежность `order_id`, `chatId` или image ID.
3. Любой authenticated user может вызвать `send_admin_to_client_painter_comments()`, записать `is_admin=1`, отправить письмо клиенту и инициировать Synvolve manager-message side effect.
4. Старый `/account` перенаправляет в новый кабинет, но его POST endpoints остаются активными и дублируют небезопасную логику.
5. Custom login/register/OAuth выполняют вход без явной regeneration session ID; custom login не имеет throttling, а стандартный `/register` обходит reCAPTCHA custom registration.
6. Custom registration отправляет введённый пароль пользователю в открытом виде по email.
7. Socialite callback использует `stateless()`, не проверяет OAuth state, связывает аккаунт только по email и не хранит provider identity.
8. Четыре chat stream имеют разные имена order key, sender/read semantics и неполную attribution; сливать их в одну таблицу без deterministic mapping нельзя.
9. AJAX добавляет текст сообщения в HTML template literal без escaping; письмо и часть webhook logs также получают пользовательский текст/PII.
10. `GET /new/set_all_painter_images` изменяет все заказы, доступен любому authenticated user и завершается `dd()`.

До закрытия FN-09 legacy production остаётся единственным write-owner identity/account/chat. Новый код допустим только в shadow-read/fake режиме.

### Решения владельца от 17.07.2026

- сохранить все восемь ролей и адаптировать фактическую permission matrix в одной Filament panel;
- сохранить self-registration и текущее отсутствие обязательной email verification;
- сохранить Facebook/Google Socialite user-facing flows; auto-link по email разрешён только при verified unique provider email, валидном state и unique provider subject;
- не выполнять массовый logout: сохранить совместимые auth/guest/cart sessions после compatibility tests, регенерировать session при login/link/privilege change;
- сохранить business behavior всех четырёх chat streams без механического слияния, одновременно закрыв IDOR/wrong-role/ownership defects.

Эти решения закрывают owner direction, но не security/runtime gates. Legacy остаётся write-owner до role×ownership, OAuth collision/state, session continuity и four-stream differential tests.

## 2. Проверенная область

Проверены:

- `Auth::routes()` и `app/Http/Controllers/Auth/*`;
- `UserManageController`: custom login, registration и reset;
- `SocialController`: Facebook/Google redirect и callback;
- `AccountController` и `Account\AccountController`;
- `Admin\VoyagerAdminController` chat methods;
- `User`, `Orders`, chat models и upload services;
- account/admin Blade и inline JavaScript;
- `config/auth.php`, `config/session.php`, `config/services.php`, `Http/Kernel.php`, `AuthServiceProvider`;
- migrations для users/chat/SA fields;
- route registry от 13.07.2026 и существующие tests;
- локальный `.env` только как presence/config-shape, без вывода secrets.

Не выполнялись:

- вход через реальные Google/Facebook;
- отправка реальных reset/registration/chat писем;
- вызов Synvolve;
- запись в chat/users/orders;
- hostile HTTP requests к рабочему сайту;
- production access-log/DB/session inspection.

## 3. Фактические auth entry points

### 3.1. Стандартный Laravel auth

`Auth::routes()` регистрирует стандартные login, logout, register, reset и password confirmation routes. `LoginController` использует framework throttling и явно регенерирует session ID после успешного входа.

`RegisterController` требует только email/password, использует minimum 8 и не применяет reCAPTCHA. Поэтому наличие reCAPTCHA в `custom_register_ajax` не защищает регистрацию в целом: тот же пользователь может зарегистрироваться через стандартный `POST /register`.

Email verification фактически не является gate:

- `Auth::routes()` вызван без `verify => true`;
- `User` не реализует `MustVerifyEmail`;
- account routes не используют middleware `verified`.

### 3.2. Custom AJAX auth

`custom_login_ajax()`:

- принимает только `email/password` без FormRequest;
- вызывает `Auth::attempt()` без route/controller throttle;
- не регенерирует session ID после входа;
- не поддерживает controlled remember flow;
- возвращает HTTP 200 и собственный JSON при неверном пароле.

`custom_register_ajax()`:

- имеет reCAPTCHA и minimum password 6;
- создаёт/логинит пользователя без session regeneration;
- отправляет `SendUserRegister($user, $request->password)`;
- mail template выводит переданный plaintext password;
- выполняет synchronous mail и abandoned-cart side effect до ответа.

`reset_password()` сосуществует со стандартным reset flow, различает `INVALID_USER` и `RESET_LINK_SENT`, не имеет HTTP throttle/captcha и не обрабатывает все статусы broker единым контрактом.

### 3.3. Смена пароля

Старый `AccountController::ajax_change_information()` меняет пароль, если передан старый или новый пароль, но не проверяет правильность `old_password`.

Новый controller проверяет старый пароль и confirmation, но не задаёт minimum/strength/compromised-password policy, не требует recent password confirmation и после смены не отзывает другие sessions/remember tokens. `AuthenticateSession` в web middleware group закомментирован.

## 4. Session/cookie baseline

Кодовый baseline:

- session driver default — database;
- session encryption — `true`;
- `http_only=true`;
- `secure` по умолчанию `false`;
- `same_site=null`;
- `AuthenticateSession` отключён.

Локальная конфигурация 14.07.2026, без раскрытия secrets:

- `APP_ENV=production`, `APP_DEBUG=true`;
- `APP_URL=https://viarcanvas.loc/`;
- `SESSION_DRIVER=database`, lifetime 1440 минут;
- `SESSION_SECURE_COOKIE` не задан;
- OAuth client ID/secret и reCAPTCHA keys заданы;
- OAuth callback URI указывает на production host, а `APP_URL` — на `.loc`.

Это не утверждение о production `.env`, а подтверждённый config-parity blocker локальной среды. Перед OAuth/session tests нужны отдельные local/staging provider applications и cookie baseline `Secure + HttpOnly + explicit SameSite`.

Read-only local DB snapshot после запуска БД:

- `users=23 801`, пустых email нет, exact и normalized duplicate email groups — 0;
- все 23 801 password hashes имеют форму bcrypt `$2y$`;
- `users.email` имеет unique index;
- `email_verified_at` заполнен у 0 users;
- `remember_token` заполнен у 1 340 users;
- `facebook_id` существует, но заполнен у 0; `google_id/provider/provider_id` отсутствуют;
- роли: user 23 778, painter 10, admin 3, printing 1, seo_manager 1, role_id NULL 8;
- `users.status`: пусто у 23 798, значение `1` у 2, `2` у 1; фактическая active/disabled семантика кодом auth не формализована;
- `password_resets=172`, все старше 24 часов;
- `sessions=341`, все guest и старше 24 часов; authenticated session в момент среза нет.

Это подтверждает совместимость существующих bcrypt hashes как migration input, но не разрешает массовый reset/rehash. Target должен выполнять verify + rehash-on-success и отдельно очистить stale reset/session rows по утверждённой retention policy.

## 5. Socialite: Facebook и Google

### 5.1. Redirect

Оба redirect routes публичны и находятся только под `web`. Facebook UI учитывает `FACEBOOK_ENABLE`, но route/controller сам не запрещает disabled provider.

`putIntended()` сохраняет return URL, locale, country, page, UA и IP в session. Проверка external URL основана на строковом prefix `strpos($return, url('/')) === 0`. Она не является строгим сравнением scheme/host/port и отдельно не запрещает protocol-relative URL.

### 5.2. Callback и account linking

Оба callbacks вызывают `stateless()->user()`. Это отключает стандартную проверку OAuth state. После callback:

- provider user ID не сохраняется;
- отдельной таблицы identities/linking нет;
- пользователь выбирается только по email через `firstOrCreate`;
- provider verified-email claim не проверяется и не сохраняется как evidence;
- новый user получает случайный local password и `email_verified_at=now()`;
- существующий local user входит по совпадающему email без явной процедуры link/confirm;
- при каждом входе перезаписываются country, settings.locale, registration_page, user_agent и last_ip;
- `Auth::login($user)` не сопровождается явной session regeneration;
- логи содержат email и имя.

Target обязан хранить immutable `(provider, provider_subject)` и linking audit, а email использовать как подтверждённый атрибут, но не как единственный provider key.

## 6. Account read ownership

Подтверждённая положительная логика:

- client order list: `orders.user_id = Auth::id()`;
- painter order list: order ID берутся из `painter_orders` для текущего painter;
- coupons/profile читаются для текущего user;
- повторная оплата выбирает order одновременно по `id` и `user_id`, painter получает 404.

Эта логика является characterization baseline и должна быть перенесена в Policies/query scopes без расширения видимости.

## 7. Account write ownership

Следующие auth routes принимают ID из request и не повторяют ownership/role scope из read path:

- painter image/sketch upload;
- painter comment;
- client/painter/admin comment;
- painter/admin order chat;
- image status change;
- три read-state endpoint;
- attachment/image-thread linkage.

Следствия:

- client может указать чужой `order_id`;
- client может вызвать painter-only action;
- painter может указать заказ, на который не назначен;
- любой authenticated user может обновить произвольный `chatId`;
- `status_name`, image flags и image ID приходят от клиента без allowlist/ownership;
- side effect email/webhook может быть отправлен уже для чужого заказа.

CSRF защищает от cross-site form submission, но не от IDOR и wrong-role request с валидной собственной session.

## 8. Публичный user lookup

`POST /admin/check-user` находится вне auth/admin group. Метод:

- разрешает `%email%` поиск;
- не валидирует длину/формат;
- не ограничивает количество строк;
- не throttled;
- читает через `DB::table('users')->get()`;
- возвращает строки напрямую в JSON.

Поскольку это не Eloquent model serialization, `User::$hidden = ['password','remember_token']` не применяется. Endpoint должен считаться P0 data exposure до отдельного safe HTTP test и немедленного deny-by-default решения.

## 9. Четыре legacy chat stream

| Stream | Фактическое назначение | Sender/direction | Главный разрыв |
|---|---|---|---|
| `order_user_comments` | основной client/manager/painter stream; CRM/SA mirror | `user_id`, `is_admin`, read flags, image flags, SA fields | смешаны разные акторы; ownership не проверяется на legacy writes |
| `orders_chats` | painter ↔ admin | `is_admin`; новые inserts не сохраняют author user ID | нельзя доказать конкретного sender; legacy SA columns не означают source of truth |
| `order_admin_comments` | внутренние admin comments | `user_id`, `orders_id` | controller требует только auth, не admin role/order permission |
| `order_painter_comments` | legacy painter comments | insert содержит order/comment/time | sender/assignment/read semantics отсутствуют |

`sa_messages`/`sa_conversations` — отдельный integration ledger, а не пятый UI stream для механического слияния. `order_user_comments` остаётся client-visible source согласно AGENTS/CRM documentation; SA rows связываются stable external IDs.

## 10. Несогласованность схемы

В migrations найдены:

- создание `order_admin_comments` и последующее добавление `user_id`;
- alter migrations для read/image flags `orders_chats` и `order_user_comments`;
- SA fields для `orders_chats`;
- нет найденных base create migrations для `orders_chats`, `order_user_comments` и `order_painter_comments`.

Следовательно, fresh migration не является доказанным источником production chat schema. До target migration нужен `SHOW CREATE TABLE`, indexes/FK/nullability/collation snapshot и exact counts всех четырёх потоков.

Read-only exact snapshot 14.07.2026:

| Stream | Rows | Orders | Null/orphan order | Attribution/flags |
|---|---:|---:|---:|---|
| `order_user_comments` | 3 835 | 1 519 | 0 / 120 | client 3 202; staff 542; assigned painter 18; other user 73; 1 missing author |
| `orders_chats` | 2 840 | 1 409 | 40 / 49 | author ID отсутствует; `is_admin=1` у 2 484; SA ID заполнен у 0 |
| `order_admin_comments` | 106 | 92 | 0 / 5 | `user_id` NULL у 8 |
| `order_painter_comments` | 25 | 21 | 0 / 9 | author field отсутствует |

`order_user_comments` дополнительно содержит 542 `is_read=0` и 2 518 `admin_is_read=0`. В `orders_chats` — 380 `is_read=0` и 571 `admin_is_read=0`. Это legacy flags, а не доказательство фактического непрочтения: текущие arbitrary-ID read endpoints могли менять их вне owner scope.

У всех четырёх legacy streams отсутствуют foreign keys. `order_user_comments`, `order_admin_comments` и `order_painter_comments` имеют только primary index; у `orders_chats` кроме primary есть лишь indexes SA fields, но нет order index. Orphan rows нельзя автоматически удалять: часть может быть намеренно сохранённой историей удалённых orders/users. Нужна классификация и reconciliation, не cleanup «по факту orphan».

Локальные `sa_messages` и `sa_conversations` существуют, имеют ожидаемые unique/indexes, но содержат 0 rows. Это local snapshot, не production volume evidence.

## 11. Read flags

`is_read` и `admin_is_read` меняются тремя endpoint по одному `chatId` без order/user scope. Направление определяется текущей ролью или выбирается отдельным route, а не типизированной state machine.

Target contract:

- read receipt содержит `stream`, `message_id`, `reader_user_id`, `reader_role`, `read_at`;
- message сначала выбирается внутри разрешённого conversation/order scope;
- повторный mark-read идемпотентен;
- чужой message возвращает 404/403 и не меняет state;
- unread counters считаются по тому же contract, что и UI.

## 12. Duplicate, ordering и partial failure

Legacy chat writes не имеют client message ID/idempotency key. Double click/retry создаёт повторную DB row и может повторить email/Synvolve.

Порядок side effects различается:

- часть методов сначала отправляет email, затем пишет DB;
- admin-to-client сначала пишет DB/email, затем вызывает Synvolve;
- webhook failure логируется и возвращается как `false`, но controller его результат игнорирует;
- transaction/outbox/attempt receipt нет.

Target: DB transaction для message + outbox, unique client message key, deterministic external message ID, retry worker и reconciliation статусы `pending|sent|failed|unknown`.

## 13. Validation, attachments и content safety

Для message text нет единого FormRequest, required/maximum/content contract. Image flags и связанный painter image ID не проверяются на принадлежность тому же order.

Upload services проверяют extension/MIME частично, но вызываются до подтверждения actor/order ownership. Painter upload max задан как 9 000 000 KB, что фактически не является разумным application limit.

Blade reload экранирует comment text, однако success callbacks используют `${response.comment}`, `${msg.comment}` и user input внутри `.append()` HTML. Это создаёт DOM-XSS/HTML-injection path сразу после отправки. Несколько email paths передают comment в `setBody(..., 'text/html')` без escaping. Synvolve service логирует полный payload с phone/text и provider body.

## 14. State-changing GET и consent ownership

`GET /new/set_all_painter_images`:

- находится под обычным `auth`;
- обходит role/permission;
- перебирает все orders;
- вставляет/обновляет `order_painter_images`;
- заканчивается `dd('ок')`.

`GET /user/{id}/unsubscribe` публично меняет `users.news` по переданному ID без signed token или authenticated ownership.

Оба контракта должны быть закрыты: bulk backfill переносится в CLI/job с dry-run/lock/checkpoint, unsubscribe — в signed opaque token или authenticated self-service POST.

## 15. Target authorization model

Нужны Policies и typed actor matrix:

| Action | Client | Assigned painter | Manager/admin | Другой authenticated user |
|---|---|---|---|---|
| Просмотр client order | только свой | только назначенный operational subset | по permission | deny |
| Client message | только свой order | только назначенный и явно разрешённый stream | по permission | deny |
| Painter/admin stream | deny | только назначенный order | по permission | deny |
| Internal admin note | deny | deny | по permission | deny |
| Painter upload/status | deny, кроме client acceptance action | только назначенный order | по permission | deny |
| Mark read | только видимое сообщение | только видимое сообщение | только видимое сообщение | deny |
| Reprint/payment/account settings | self/owned contract | deny или отдельный contract | permission | deny |

UI role checks не являются security boundary. Каждый query/write должен начинаться с Policy-approved scope.

## 16. Target identity model

Минимальная additive схема:

- `user_identities`: `user_id`, `provider`, `provider_subject`, normalized email snapshot, verified_at, linked_at, last_login_at, metadata hash;
- unique `(provider, provider_subject)`;
- explicit link/unlink/recovery audit;
- provider email collision переводится в confirm-link flow, а не автоматический login;
- existing password hashes проверяются legacy hasher и rehash-on-success;
- password/session security events пишутся без secrets/PII payload;
- disabled/deleted/role-changed user sessions отзываются.

Нельзя создавать вторую users table или менять существующие `users.id`: они связаны с orders, chats, painter assignments, coupons и audit data.

## 17. Target message mapping

До унификации вводится adapter, а не destructive merge:

- stable stream enum: `client_manager`, `painter_admin`, `internal_admin`, `legacy_painter_note`, `sa_native`;
- source table/source row ID сохраняются;
- sender actor и direction вычисляются детерминированно, ambiguous rows маркируются `unknown`, а не угадываются;
- order ID сохраняется;
- legacy read flags переводятся через documented mapping;
- image/thread association валидируется и сохраняется immutable snapshot;
- SA mirror связывается `sa_message_id`, не дублируется по тексту/времени;
- cutover идёт по одному stream с одним write-owner.

## 18. Обязательные тесты до реализации

### Auth/session

- standard и custom login: success/failure/throttle/session ID regeneration;
- registration routes: единый captcha/consent/password/email verification contract;
- reset enumeration/throttle/token reuse/expiry;
- password change: old password, strength, session revocation;
- disabled/deleted/role-changed user;
- Secure/HttpOnly/SameSite cookie и session fixation.

### OAuth

- state mismatch/absent callback;
- strict return URL allowlist;
- provider no-email/unverified-email;
- existing local email collision;
- same provider subject with changed email;
- different provider with same email;
- link/unlink/recovery;
- locale/country preservation for existing account;
- disabled provider route.

### Ownership/chat

- anonymous, client own/foreign, assigned/unassigned painter, manager/admin, wrong role;
- every write endpoint with foreign `order_id`, `chatId`, image ID;
- forged `is_admin`, image flags, status name;
- duplicate/concurrent/replay;
- email/webhook failure before/after DB commit;
- XSS/HTML, long text, Unicode and seven locales;
- attachment MIME/size/count/ownership;
- read counters and receipts;
- deterministic four-stream mapping and rollback.

## 19. DoR / exit gate FN-09

FN-09 готов к реализации только после:

1. немедленного закрытия/контрактного ограничения `/admin/check-user`;
2. утверждённой role/order/chat/image Policy matrix;
3. isolated staging с blocked mail/provider side effects;
4. production-like DB schema/count/index/hash/session metadata;
5. provider sandbox applications и callback/return allowlist;
6. решения по account-linking и email verification;
7. characterization tests четырёх stream и текущих unread counters;
8. idempotency/outbox/reconciliation design;
9. content escaping и private attachment contract;
10. cutover/rollback с legacy sole writer и сохранением всех языков/переводов.

## 20. Риски FN-09

- **R-56 Critical** — публичный user lookup может раскрыть password hashes, remember tokens и PII.
- **R-57 Critical** — auth-only account mutations позволяют IDOR/wrong-role writes в чужой order/chat/file/status.
- **R-58 Critical** — authenticated user может выдавать сообщение за admin и инициировать email/Synvolve side effect.
- **R-59 High** — параллельные auth routes дают разные captcha/password/throttle/session contracts; plaintext password отправляется по email.
- **R-60 High/Critical** — OAuth без state и provider identity, email-only linking и слабая return validation создают account-link/redirect/session risk.
- **R-61 High** — четыре stream имеют неоднозначный sender/read mapping и неполную migration schema; destructive merge потеряет историю.
- **R-62 High** — отсутствие idempotency/outbox создаёт duplicate messages и partial DB/email/webhook outcomes.
- **R-63 High** — DOM/HTML injection и PII payload logging затрагивают chat, email и integration logs.
- **R-64 Critical** — state-changing GET bulk backfill и unsigned unsubscribe допускают неавторизованные изменения.
- **R-65 High** — session/cookie/debug/provider/DB config parity не доказана; runtime gate нельзя считать пройденным.

## 21. Следующий шаг

1. На изолированном staging закрыть P0 HTTP matrix для `/admin/check-user` и всех 17 account/chat mutations без реальных писем/webhooks.
2. Снять production-like `SHOW CREATE TABLE`, volume/retention/access-log metadata и сверить с уже полученными local aggregates/indexes.
3. Получить Google/Facebook sandbox clients и выполнить state/linking/collision tests.
4. После FN-09 L3 перейти к FN-10: mail, queue, jobs и scheduler.
