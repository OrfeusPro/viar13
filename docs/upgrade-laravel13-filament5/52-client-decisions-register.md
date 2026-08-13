# 52. Реестр решений клиента по миграции Laravel 13 / Filament 5

Дата фиксации: 16.07.2026.

Статус: опрос завершён — получены и зафиксированы ответы на все 68 вопросов. Подтверждённые решения являются входными условиями плана и клиентского DOCX; дальнейшие уточнения выполняются перед запуском и приёмкой конкретных этапов.

Execution update 17.07.2026: существующий `test.viarcanvas.com` разрешено полностью заменить и использовать как технический staging для Laravel 13 / Filament 5. Загружается отдельный target, не текущий Laravel 6; production и клиентский функциональный scope не меняются.

## 1. Цель и границы проекта

| ID | Вопрос | Подтверждённое решение | Влияние на план и критерий приёмки |
|---|---|---|---|
| DEC-SCOPE-01 | Полный или частичный отказ от Voyager | Выполняется полный функциональный переход с Voyager на Filament. Работа идёт поэтапно; цель — убрать зависимость от неподдерживаемого на новых Laravel Voyager и сохранить возможность дальнейших обновлений Laravel. | Voyager отключается только после переноса функций, проверки parity и rollback window. Установка Filament сама по себе не считается миграцией. |
| DEC-SCOPE-02 | Приоритет переноса | 1) заказы и CRM; 2) пользователи и роли; 3) чаты; 4) каталог и цены; 5) переводы; 6) платежи и доставка; 7) контент, блог, галерея и SEO. | Детальная декомпозиция и staging UAT строятся в указанной очередности. Связанные write-owner и интеграционные зависимости закрываются до включения записи каждого модуля. |
| DEC-SCOPE-03 | Что можно архивировать | По умолчанию переносится всё, что сейчас доступно в меню админ-панели. Функции не удаляются только на основании отсутствия очевидного использования в коде или локальной БД. | Scope baseline включает 138 admin menu items для всех ролей, связанные BREAD/custom pages/actions и фактически используемые прямые workflow. Архивирование допускается только после production usage evidence и отдельного решения владельца. |
| DEC-SCOPE-04 | Одна или несколько панелей | Сохраняется одна общая административная панель с различной видимостью и правами, как сейчас. | В target создаётся один Filament Panel. Переносятся восемь ролей, матрица 675 permissions и 2 080 role links с явным mapping на Resources/Pages/Actions. Видимость меню не заменяет server-side authorization. |
| DEC-SCOPE-05 | Дизайн и UX | Допустим новый стандартный дизайн Filament, но должны сохраниться все текущие функции и бизнес-поведение. | Pixel-perfect копирование Voyager UI не требуется. Обязательны functional parity, сохранение данных, ролей, фильтров, действий, переводов, загрузок и side effects; UX-изменения не должны менять бизнес-результат без отдельного согласования. |

## 2. Подтверждённая техническая интерпретация DEC-SCOPE-04

Текущий production baseline содержит:

- одну Voyager admin surface;
- 138 пунктов admin menu;
- 133 BREAD definitions и 1 997 field definitions;
- восемь ролей: `admin`, `user`, `painter`, `manager`, `Admin 2`, `printing`, `lite_manager`, `seo_manager`;
- 675 permissions и 2 080 назначений permission-role;
- 40 custom Voyager views и 52 custom route declarations после `Voyager::routes()`.

Следовательно, перенос одной панели требует трёх независимых проверок:

1. пункт виден только разрешённым ролям;
2. прямой URL закрыт для anonymous и wrong-role;
3. каждое действие, изменение, загрузка, экспорт и bulk operation проверяет отдельную ability и ownership.

## 3. Подтверждённая граница переноса меню и связанных функций

Подтверждено: в обязательный перенос входят функции, которые не представлены отдельным пунктом меню, но вызываются из заказов, кнопок, прямых URL или интеграций. Переносятся все активные production workflow, достижимые из меню или связанных действий; stale metadata архивируется только после evidence и owner sign-off.

## 4. Сроки, оценка и ответственность

| ID | Вопрос | Подтверждённое решение | Влияние на план и критерий приёмки |
|---|---|---|---|
| DEC-PLAN-06 | Желаемый срок | 01.10.2026 используется как рабочий ориентир, а не fixed deadline полного отказа от Voyager. Прогноз пересматривается после каждого этапа по фактической скорости. | Базовая цель — закрыть подготовительный этап и первый приоритетный increment `Заказы и CRM` на staging; если работы идут быстрее, scope milestone расширяется только после UAT и rollback evidence. Скорость не является основанием пропускать security/data gates. |
| DEC-PLAN-07 | Формат оценки | Нужна отдельная оценка каждого этапа. | Для каждой волны готовятся scope, зависимости, диапазон трудозатрат, команда, риски, критерии приёмки и rollback. Общая сумма не используется как fixed-price до закрытия gates этапа. |
| DEC-PLAN-08 | Кто принимает окончательные решения | Сам клиент. | Клиент является Business/Product sign-off owner и принимает scope, приоритеты, content decisions и go/no-go. |
| DEC-PLAN-09 | Кто участвует в приёмке направлений | Программист. | Программист координирует техническую проверку и evidence. Финальная бизнес-приёмка остаётся у клиента; для платежей, переводов, доставки и SEO рекомендуется получить подтверждение фактического владельца процесса либо явное делегирование клиентом. |
| DEC-PLAN-10 | Поэтапное отключение Voyager | Согласовано. Voyager отключается частями только после проверки соответствующего модуля. | Для каждой волны обязательны parity/UAT, final delta, один write-owner, observation window и проверенный rollback. |

## 5. Рабочая интерпретация срока

Рабочая цель до 01.10.2026:

1. завершить подготовку: staging, backup/restore rehearsal, CI, baseline tests и декомпозицию всех этапов;
2. подготовить первый рабочий increment `Заказы и CRM` на staging/read-only;
3. при успешных UAT и rollback rehearsal рассмотреть ограниченный production cutover согласованной части `Заказы и CRM`;
4. не обещать полный отказ от Voyager к 01.10.2026: текущим аудитом это не подтверждается без существенного сокращения scope, усиления команды и пересмотра рисков.

Срок является rolling forecast: после завершения каждого этапа сравниваются плановые и фактические трудозатраты, обновляются оценки следующих волн и доступный scope до 01.10.2026.

## 6. Production cutover и staging

| ID | Вопрос | Подтверждённое решение | Влияние на план и критерий приёмки |
|---|---|---|---|
| DEC-CUT-11 | Допустимая техническая пауза | 0 минут. | Проектируется zero/near-zero downtime: legacy остаётся доступным до health/parity gate, переключение выполняется по компонентам. Нельзя использовать длительный global write freeze или восстановление старого DB dump поверх новых заказов. |
| DEC-CUT-12 | Время переключения | Выходной день. | Конкретные дата, час, timezone, ответственный и rollback deadline фиксируются в runbook каждой волны. До переключения требуется production traffic baseline за выбранный выходной интервал. |
| DEC-STG-13 | Отдельный staging PHP 8.3/8.4 | Разрешён с копией production без персональных данных. | Staging изолируется от production writers/providers, получает sanitized DB/files и воспроизводимый target runtime. Анонимизация не должна ломать связи order/user/chat/file. |
| DEC-STG-14 | Тестовые операции на staging | Разрешены тестовые заказы, оплаты, чаты и переводы. | Используются synthetic/sanitized fixtures, sandbox providers и outbound deny/sink. Тестовые события не должны отправлять production письма, webhooks, платежи или courier requests. |
| DEC-CUT-15 | Кто присутствует при переключении | Программист. | Программист является техническим cutover operator и выполняет smoke/reconciliation/rollback. Поскольку final Business/Product owner — клиент, business go/no-go должен быть подписан заранее; для спорного бизнес-результата нужен заранее согласованный stop/rollback rule. |

## 7. Обязательные следствия нулевой паузы

1. Legacy production остаётся system of record до переключения конкретного модуля.
2. Для каждого модуля одновременно пишет только один UI: Voyager либо Filament.
3. Перед и после переключения выполняются final delta и reconciliation новых заказов, платежей, сообщений, файлов и переводов.
4. Rollback возвращает application/write owner на legacy, но не откатывает старый общий DB dump поверх новых production writes.
5. Все provider side effects используют idempotency/outbox/receipt либо остаются на legacy до доказанной parity.
6. Переключение отменяется при unexplained data drift, permission escalation, duplicate/lost event, payment uncertainty или невозможности безопасного возврата владельца записи.

## 8. Подтверждённое организационное условие переключения

Если во время cutover присутствует только программист, клиент до окна переключения подписывает ожидаемые бизнес-результаты, допустимые отклонения и право программиста выполнить rollback без дополнительного ожидания. Рекомендуется, чтобы клиент оставался доступен по связи как escalation owner, даже если не участвует в технических действиях.

## 9. Окно, резервная копия и внешние sandboxes

| ID | Вопрос | Подтверждённое решение | Влияние на план и критерий приёмки |
|---|---|---|---|
| DEC-CUT-16 | Предпочтительный день | Суббота. | Все per-wave cutover runbooks планируются на субботу; перед назначением снимается traffic/provider baseline того же временного интервала. |
| DEC-CUT-17 | Предпочтительное время | Вечер. | Точный час и business timezone ещё нужно зафиксировать. Server UTC и business time явно указываются в runbook, логах и checkpoint. |
| DEC-CUT-18 | Доступность клиента | Клиент доступен по телефону/мессенджеру во время переключения. | Программист выполняет технические действия; клиент является escalation и Business/Product go/no-go owner. Канал связи и максимальное время ожидания ответа фиксируются заранее. |
| DEC-BAK-19 | Ручная копия перед переключением | Подтверждена полная ручная копия БД и файлов перед каждым критическим переключением. | Для копии сохраняются UTC timestamp, размеры, checksums, DB/file counts, место хранения и ответственный. Копия не заменяет final delta/reconciliation. |
| DEC-BAK-20 | Изолированное тестовое восстановление | Клиент ответил, что обязательное восстановление перед первым переключением не требуется. | Зафиксирован высокий остаточный риск: непроверенная копия не доказывает recoverability или RPO/RTO. Старый dump не используется для application rollback поверх новых orders/payments/chats/translations. Data-changing migration/catastrophic recovery остаются без доказанного restore path; техническая рекомендация выполнить restore drill сохраняется. |
| DEC-INT-21 | Sandbox/test credentials | PayPal, Paysera, Venipak, Synvolve/SA и SMTP sandbox-доступов сейчас нет. | Staging использует fakes, recorded sanitized fixtures, contract tests и outbound deny. Реальные provider writes остаются на legacy до отдельно согласованного минимального production smoke и reconciliation; blind retry запрещён. |

## 10. Остаточные риски решений 16–21

1. **Backup risk:** наличие архива без успешного восстановления не подтверждает, что он полный, читаемый и восстанавливается в допустимое время.
2. **Zero-downtime rollback:** основной rollback должен возвращать маршрутизацию/write-owner на актуальный legacy, а не восстанавливать старую БД.
3. **Provider risk:** без sandbox нельзя доказать capture, callback, label, webhook и mail behavior только локальными mocks.
4. **Integration sequencing:** PayPal/Paysera/Venipak/Synvolve/SMTP переключаются позже доменной логики и только после contract tests, idempotency, provider receipts и controlled live smoke.
5. **Schedule impact:** отсутствие sandbox и restore drill может увеличить сроки соответствующих этапов и не должно компенсироваться пропуском reconciliation/UAT.

## 11. Provider ownership и решения по переводам

| ID | Вопрос | Подтверждённое решение | Влияние на план и критерий приёмки |
|---|---|---|---|
| DEC-CUT-22 | Точный Saturday window | Подтверждено `20:00 Europe/Riga` как рабочее время переключения. | Runbook дополнительно указывает соответствующее UTC-время и проверяет timezone серверов, очередей, БД и журналов. |
| DEC-INT-23 | Реальные provider operations до проверки | PayPal, Paysera, Venipak, SMTP и Synvolve/SA остаются на legacy до отдельной проверки каждой интеграции. | Target использует fakes/read-only shadow. Переключение каждого provider требует idempotency, receipts, controlled live smoke и reconciliation unknown outcomes. |
| DEC-I18N-24 | Кто утверждает переводы | Программист. | Программист является делегированным content/translation owner; клиент сохраняет final Business/Product authority. |
| DEC-I18N-25 | Приоритет источника при расхождении | Основным считается действующий production-перевод. | Для `header.top_sale` в DB/PHP и RU `orders.user_status.print_text` принято `production wins`; локальная версия не перезаписывает production автоматически. |
| DEC-I18N-26 | `orders.not_specified` | Сохранить. | Ключ остаётся dormant во всех восьми основных file locales и проходит empty/fallback UAT; автоматическое удаление запрещено. |
| DEC-I18N-27 | `cn/jp google_reviews.php` | Не нужны в активной системе. | CN/JP не активируются как public locale. Файлы сохраняются byte-identical до usage proof, затем архивируются/retire отдельным решением; массовое удаление во время merge запрещено. |
| DEC-I18N-28 | Параллельное редактирование | Запрещено: один модуль редактируется либо Voyager, либо Filament. | Для каждой волны фиксируются current/target write-owner, final delta, observation window и rollback owner. |

Все 27 строк translation owner disposition теперь имеют решение. Машиночитаемый результат: `appendix-language-owner-decisions-r14.csv`. Решения имеют статус `approved pending UAT`: они определяют candidate artifact, но не разрешают production publish до выполнения 14-case staging UAT, lint/manifest и rollback rehearsal.

## 12. Пользователи, роли, Socialite, сессии и чаты

| ID | Вопрос | Подтверждённое решение | Влияние на план и критерий приёмки |
|---|---|---|---|
| DEC-AUTH-29 | Сохранение ролей | Все восемь существующих ролей и фактические права сохраняются и адаптируются в Filament. | Выполняется role×resource×action mapping 675 permissions/2 080 assignments. Права, названия и видимость не упрощаются без отдельного решения. |
| DEC-AUTH-30 | Политика новых Filament actions | Основная цель — воспроизвести текущую матрицу ролей, а не заменить её общей новой deny-policy. | Для известных действий переносится фактическое разрешение. Новое или несопоставленное действие остаётся закрытым до mapping, чтобы отсутствие legacy-аналога не создало расширение доступа. Menu visibility, прямой URL и action authorization тестируются отдельно. |
| DEC-AUTH-31 | Самостоятельная регистрация | Сохраняется. | Standard/custom entry points сводятся к одному контракту с текущим пользовательским поведением, CAPTCHA/throttle parity и без отправки plaintext password. |
| DEC-AUTH-32 | Email verification | Сохраняется текущее поведение: обязательной email verification gate нет. | Существующие пользователи не блокируются и не получают массовое требование подтверждения. Target не трактует пустой `email_verified_at` как отключённый аккаунт. Возможное введение verification — отдельный будущий проект. |
| DEC-AUTH-33 | Social login | Сохраняется текущее Socialite-поведение по доступным Facebook/Google сценариям. | Provider UI/routes включаются согласованно. Из-за отсутствия sandbox provider cutover остаётся legacy-owned до console redirect и controlled provider tests. |
| DEC-AUTH-34 | Связывание по совпадающему email | Разрешено. | Auto-link допустим только для уникального подтверждённого provider email при валидном OAuth state и сохранённом unique provider subject. No-email, unverified email, subject/email collision требуют отказа или явного подтверждения; linking получает audit trail. |
| DEC-AUTH-35 | Действующие сессии | Техническое решение делегировано программисту. Выбран безопасный вариант: не выполнять массовый logout и сохранить совместимые auth/guest/cart sessions при переключении. | До cutover проверяются cookie name/domain/path/Secure/SameSite, APP_KEY, DB session payload и cart continuity. Session ID регенерируется при следующем login/link/privilege change. Если compatibility gate не пройден, forced re-login выполняется только в отдельной auth wave после уведомления клиента, не теряя cart/order state. |
| DEC-CHAT-36 | Права и потоки чатов | Сохраняется текущее бизнес-поведение чтения/отправки сообщений. | Четыре legacy streams не сливаются механически. Переносятся intended role/ownership/read semantics, но не текущие IDOR/wrong-role дефекты: client видит свой order, painter — назначенный order, manager/admin — разрешённый scope; каждый mutation проверяет actor и ownership. |

## 13. Подтверждённая интерпретация «как сейчас»

- Email verification сейчас не является обязательным gate: `Auth::routes()` не включает verification, `User` не реализует `MustVerifyEmail`, middleware `verified` не используется; в локальном snapshot `email_verified_at` заполнен у 0 из 23 801 users.
- Текущий Facebook/Google callback использует `stateless()` и выбирает пользователя только по email. Функциональный результат сохраняется, но перенос уязвимой реализации запрещён: target обязан проверять OAuth state и хранить unique `(provider, provider_subject)`.
- Сохранение чат-поведения означает сохранение функций и допустимой видимости, а не перенос отсутствующих ownership-проверок. Любые произвольные `order_id/chatId/image_id` mutations закрываются scoped Policies.
- Session continuity является частью требования нулевой паузы, поскольку session содержит login/cart/checkout state. Массовое удаление sessions без отдельного плана запрещено.

## 14. Заказы, CRM и платежи

| ID | Вопрос | Подтверждённое решение | Влияние на план и критерий приёмки |
|---|---|---|---|
| DEC-ORD-37 | Статусы заказов | Все существующие значения сохраняются без переименования. | ID и строковые status values считаются инвариантами. Нормализация spelling/enums откладывается до отдельного post-parity проекта. |
| DEC-ORD-38 | Переходы и действия статусов | Должны работать как сейчас. | Сначала создаётся characterization matrix `from→to`, role, mail/CRM/chat/file side effects и negative transitions. Сохраняется подтверждённый бизнес-результат, но не отсутствие authorization/idempotency. |
| DEC-ORD-39 | Пути создания заказа | Сохраняются public checkout, admin creation и Synvolve/SA lead/order paths. | Все три пути получают differential fixtures для IDs, user, items, prices, discounts, delivery, status, files и side effects. Различия не унифицируются без evidence и owner approval. |
| DEC-PAY-40 | Способы оплаты | Сохраняются PayPal, Paysera/WebToPay и ручные payment requests. | Provider adapters переключаются отдельно; legacy остаётся owner до amount/currency/status/receipt/callback tests и controlled live smoke. |
| DEC-PAY-41 | Частичная оплата, переплата и возврат | Сохраняется текущее поведение. | До реализации снимается exact production characterization. Не вводится новая автоматическая агрегация parent `orders.payment_status` без доказанного legacy rule; неоднозначный outcome останавливает переключение. |
| DEC-ORD-42 | Бонусы, купоны, gift card, mail и Synvolve | Сохраняются текущие бизнес-эффекты. | Формируется side-effect matrix по каждому order/payment transition. Эффекты выполняются через transaction/outbox/receipt boundary либо остаются legacy-owned; существующие расхождения public/admin/SA не копируются вслепую. |
| DEC-PAY-43 | Повтор callback/операции | Повтор не должен выполнять эффект второй раз. | Обязательны idempotency key/provider transaction ID, atomic unique claim, duplicate response, immutable receipt и reconciliation для accepted-but-unknown. Refresh страницы `thanks` не отправляет письмо повторно. |
| DEC-CRM-44 | Идентификатор заказа/лида | `orders.id` остаётся основным номером заказа и `lead_id` для CRM. | ID не перенумеровывается и не заменяется target UUID. Временные entities связываются с реальным `orders.id` детерминированно; duplicate/replay не создаёт второй заказ. |

## 15. Интерпретация «как сейчас» для заказов и оплат

Сохранение текущего поведения означает сохранение подтверждённых входов, данных и бизнес-результатов. Оно не означает перенос следующих обнаруженных дефектов:

- разные неподтверждённые расчёты public/admin/SA;
- отсутствие общей transaction/outbox boundary в `Orders::saveOrder()`;
- повтор бонусов, gift card, писем или Synvolve при duplicate request/callback;
- Paysera cancel path, способный выполнить success write;
- отсутствие проверки PayPal/Paysera amount, currency, status и receipt;
- публичное ручное изменение payment status без корректной role policy;
- повторная отправка `Payment_successful` при обновлении страницы `thanks`.

Эти пункты сначала закрепляются negative/characterization tests, затем устраняются без изменения согласованного пользовательского результата.

## 16. Доставка, файлы и хранение

| ID | Вопрос | Подтверждённое решение | Влияние на план и критерий приёмки |
|---|---|---|---|
| DEC-DEL-45 | Текущие варианты доставки и Venipak | Сохраняются. | Public delivery options, stored order delivery snapshot и admin shipment/label flows входят в parity scope. Venipak остаётся legacy-owned без sandbox до controlled live smoke. |
| DEC-DEL-46 | Права на courier/label operations | Адаптируются как сейчас по ролям. | Intended role behavior фиксируется staging ACL matrix. Текущие маршруты только с `web` не считаются разрешённой parity: target требует role/ability, order scope, actor audit и idempotency. |
| DEC-DEL-47 | Поведение при Venipak outage | Сохранить текущее поведение. | До реализации снимается runtime characterization success/timeout/error/empty response. Не вводятся новые fallback-варианты без решения; target не должен принимать browser price без server verification или зависать без timeout. |
| DEC-FILE-48 | Private order/customer documents | Отклонено: сохраняется текущая публичная модель доступа; обязательный authenticated download не вводится в рамках базовой миграции. | Зафиксирован высокий residual confidentiality risk для predictable invoice/order/customer/SA paths. Формальный risk sign-off требуется до production cutover. Миграция не должна расширять публичную область или создавать новые predictable classes. |
| DEC-FILE-49 | Права на файлы | Сохраняется intended role model: клиент — свои заказы; художник — назначенные; менеджер/администратор — разрешённый scope. | Каждый upload/download/delete/rename проверяет actor, order ownership/assignment и file class. Текущие arbitrary-ID/public-path gaps не переносятся. |
| DEC-FILE-50 | Срок хранения | Сохраняется текущее поведение без новой автоматической очистки. | Existing files/messages не удаляются по возрасту. Retention/legal hold остаётся residual privacy/storage risk и может быть оформлен отдельным этапом. |
| DEC-FILE-51 | Антивирус | Антивирусное сканирование не требуется. | Обязательными остаются private quarantine, extension+real MIME+decode validation, size/count/dimension/page/duration limits, canonical path containment и безопасное имя. AV omission фиксируется как residual risk. |
| DEC-FILE-52 | Форматы и размеры | Сохраняется пользовательская совместимость текущих форматов и размеров. | Exact production corpus/limits снимаются до реализации. Legacy `MIME *`, raw SVG или проверка размера после полного download не считаются корректным limit contract; target принимает необходимые форматы через явный allowlist без уменьшения подтверждённых рабочих лимитов. |

## 17. Принятое ограничение по приватности файлов

Владелец решил не переводить order/customer/chat/invoice/gift-card/SA материалы на обязательную authenticated delivery в рамках базовой миграции. Текущая URL-совместимость сохраняется.

Компенсирующие меры, которые не меняют принятое пользовательское поведение:

1. canonical root containment и запрет path traversal/symlink escape;
2. отключённый directory listing и явный allowlist обслуживаемых roots/extensions;
3. корректные `Content-Type`, `Content-Disposition` и `X-Content-Type-Options`;
4. отсутствие новых публичных file classes и новых предсказуемых URL без отдельного решения;
5. access/error logging без PII, rate/anomaly monitoring и проверка отсутствия индексации чувствительных документов;
6. отдельный formal risk acceptance до production wave и возможность вынести private delivery в последующий security stage.

Эти меры уменьшают технические способы обхода, но не устраняют основной риск: пользователь, знающий или угадавший прямой URL, может получить файл без проверки ownership.

## 18. Frontend, SEO и приёмка

| ID | Вопрос | Подтверждённое решение | Влияние на план и критерий приёмки |
|---|---|---|---|
| DEC-FE-53 | Публичный frontend | Не переводится на Vite, не пересобирается и не получает редизайн в рамках миграции. | Текущие CSS/JS/images и `mix-manifest.json` переносятся byte-identical; фиксируются SHA-256 и load order. Filament theme собирается изолированно и не подключается к public layout. |
| DEC-SEO-54 | URL/SEO/feed continuity | Все существующие URL, redirects, canonical, sitemap и товарные feeds сохраняются. | Обязательны old/new crawl diff, redirect chain/loop test, seven-locale canonical/hreflang, XML/feed count/price/schema fixtures и 404/5xx monitoring. |
| DEC-SEO-55 | Кто принимает SEO | Программист. | Программист является technical/SEO acceptance owner; клиент сохраняет final Business/Product sign-off. |
| DEC-SEO-56 | Внешние инструменты | Доступ к Google Search Console и кабинетам товарных площадок имеется. | До/после SEO wave снимаются coverage/404/crawl/feed diagnostics без копирования credentials в репозиторий или документы. |
| DEC-UAT-57 | Языковой охват | Публичный сайт и административные сценарии проверяются на всех семи основных public locales. | UAT/golden corpus обязателен для `lv|lt|pl|ru|de|en|ee`; отдельные `et`, dormant `cn|jp` и CRM `uk→ru` проверяются по своим контрактам, но не активируются публично. |
| DEC-UAT-58 | Протокол приёмки | Для каждого этапа нужен письменный протокол `PASS/FAIL`. | Протокол содержит build/artifact ID, environment, role, locale, fixture/order ID без PII, expected/actual, evidence, defects, residual risks, sign-off и rollback result. |
| DEC-CUT-59 | Минимальное наблюдение волны | 1 день. | При component cutover Voyager write отключается сразу, Filament становится единственным writer, Voyager остаётся read-only/rollback-capable. Через 24 часа волна может быть признана стабильной только при достаточном event sample и зелёных metrics/reconciliation; legacy artifacts не удаляются. |
| DEC-OBS-60 | Мониторинг после переключения | Программист наблюдает логи и ошибки минимум 48 часов. | Первые 24 часа — активное observation window, следующие минимум 24 часа — усиленный мониторинг. Alerts покрывают HTTP, auth/ACL, DB, queue, scheduler, payments/providers, files, disk/memory и data drift. |

## 19. Ограничение однодневного observation window

Один календарный день может быть достаточен только при репрезентативном количестве операций. Для редких callbacks, платежей, доставки, weekly jobs или малотрафиковых модулей отсутствие ошибки за 24 часа не доказывает parity. В таких волнах gate закрывается по числу подтверждённых сценариев/событий, а не только по времени; rollback-capable legacy code и read-only UI сохраняются минимум до следующего стабильного релиза.

## 20. Организация, оценка и отчётность

| ID | Вопрос | Подтверждённое решение | Влияние на план и критерий приёмки |
|---|---|---|---|
| DEC-PM-61 | Исполнитель | Рабочее допущение: проект выполняет один программист. | Calendar forecast строится без параллельных developer streams; внешнее ожидание клиента/providers и weekly reporting учитываются отдельно. При подключении команды оценки пересматриваются. |
| DEC-PM-62 | Единица оценки | Часы. | Для каждого этапа указывается диапазон инженерных часов, а не одна точная цифра. |
| DEC-PM-63 | Стоимость | Не указывается; клиенту предоставляются только примерные сроки и трудозатраты. | Документ не содержит цены/fixed budget. Коммерческий расчёт выполняется отдельно при необходимости. |
| DEC-PM-64 | Отчётность | Один раз в неделю. | Weekly report содержит выполненное, фактические часы, evidence/tests, риски, отклонение прогноза, план следующей недели и необходимые решения клиента. |
| DEC-PM-65 | Новые пожелания | Оцениваются отдельно и могут менять срок. | Change request получает scope, часы, зависимости, риск и влияние на milestone до включения в работу. |
| DEC-PM-66 | Приёмка и оплата | По этапам. | Каждый этап закрывается письменным PASS/FAIL/sign-off и отдельным результатом; порядок оплаты определяется вне технического документа. |
| DEC-RISK-67 | Известные остаточные риски | Подтверждены: public file delivery, отсутствие restore drill, отсутствие provider sandboxes. | Риски явно включаются в клиентский документ и per-wave go/no-go. Подтверждение не отменяет компенсирующие controls и stop criteria. |
| DEC-DOC-68 | Обновление клиентского DOCX | Подтверждено; выпустить следующую версию с решениями, почасовыми оценками и рисками без визуального QA. | Выполняются content/structure/a11y checks. Визуальный render/PNG review намеренно пропускается по указанию пользователя и не должен считаться пройденным. |

## 20.1. Дополнительное execution-решение после исходного опроса

| ID | Вопрос | Подтверждённое решение | Влияние на план и критерий приёмки |
|---|---|---|---|
| DEC-STG-69 | Когда переносить target на сервер | Только в самом конце, когда локальная админ-панель уже будет рабочей. | Используется local-first sequence: `S0-03…S0-08` выполняются в `G:\OSPanel\home\viar_filament`; `S0-02B…S0-02D` находятся в `HOLD` до рабочей local admin и отдельного GO. Server/staging/UAT/rollback gates не отменяются и закрываются до Stage 0 sign-off/production. |

## 21. Почасовая рамка для одного программиста

| Этап | Диапазон, часов | Ориентир при 40 ч/нед. |
|---|---:|---:|
| 0. Foundation: staging, security containment, backup/config/CI/test baseline | 160–280 | 4–7 недель |
| 1. Заказы и CRM | 200–360 | 5–9 недель |
| 2. Пользователи, роли и авторизация | 100–180 | 2,5–4,5 недели |
| 3. Чаты | 80–160 | 2–4 недели |
| 4. Каталог и цены | 140–260 | 3,5–6,5 недели |
| 5. Переводы | 80–140 | 2–3,5 недели |
| 6. Платежи, доставка и provider cutover | 120–220 | 3–5,5 недели |
| 7. Контент, блог, галерея и SEO | 160–300 | 4–7,5 недели |
| 8. Общий UAT, cutover waves и decommission Voyager | 120–180 | 3–4,5 недели |
| **Итого** | **1 160–2 080** | **29–52 инженерные недели** |

Диапазоны включают реализацию, тесты, документацию и технический rollback для соответствующей волны, но не включают неизвестное ожидание external provider approvals, недоступность production/staging, новые пожелания и устранение ранее неизвестных production-only дефектов.

## 22. Рабочий ориентир до 01.10.2026

Для одного программиста безопасный базовый milestone: закрыть foundation и подготовить рабочий staging/read-only increment `Заказы и CRM`. Limited production cutover части этапа допускается только при нижней границе фактических трудозатрат и полном PASS security/data/UAT/rollback gates. Полный Voyager decommission к этой дате не обещается; прогноз обновляется еженедельно.
