# 51. L3-G06: пакет решений владельца переводов и staging UAT

Дата: 16.07.2026. Статус обновлён 17.07.2026: **technical evidence complete; 27 owner decisions captured; staging UAT подготовлен, но не выполнен**.

## 1. Цель этапа

R5–R14 закрыли поиск неизвестных расхождений. Этот этап переводит 27 технических decision rows в конкретные экраны, условия и доказательства приёмки. Он не меняет production и не выбирает тексты за владельца контента.

Источник вопросов: [appendix-language-owner-disposition-matrix-r14.csv](appendix-language-owner-disposition-matrix-r14.csv). Подтверждённые решения: [appendix-language-owner-decisions-r14.csv](appendix-language-owner-decisions-r14.csv). Машиночитаемый UAT: [appendix-language-owner-uat-protocol-after-r14.csv](appendix-language-owner-uat-protocol-after-r14.csv).

## 2. Подтверждённые runtime consumers

### 2.1 `header_footer_new.header.top_sale`

Прямые consumers:

- `resources/views/theme/viar/layouts/app.blade.php:415-421` — общий layout; вывод только при `setting('site.top_sale')=true`;
- `resources/views/layots/head.blade.php:244-248` — отдельная ветка `delivery_page`; тот же feature setting;
- `resources/views/all_styles.blade.php:151-154` — дополнительный view consumer.

Следствие: выбор текста проверяется на восьми locale и минимум в двух runtime layout paths. Отдельно проверяется OFF-ветка, чтобы изменение текста/артефакта не включило баннер и не изменило offset header.

### 2.2 `account_new.orders.user_status.print_text`

Прямой consumer: `resources/views/theme/viar/account/order.blade.php:476-512`.

Сообщение показывается только если одновременно:

- пользователь не имеет роль `painter`;
- `orders.status = in_production`;
- `App\Models\User::getPainterByOrderId(order.id)` вернул назначенного художника.

Следствие: обязательны одна positive и минимум две negative fixtures. Проверять только generic account page недостаточно.

### 2.3 `account_new.orders.not_specified`

Прямое статическое обращение в `app|resources|routes` не обнаружено. При этом ключ существует локально во всех восьми основных file locales. Dynamic translation path, fallback или незакоммиченный/production consumer статический поиск не исключает.

Безопасное решение до owner proof: сохранить ключ в candidate artifact, выполнить isolated lookup и empty-field fixture, не публиковать и не удалять автоматически.

### 2.4 `cn/jp google_reviews.php`

Группа `google_reviews` имеет подтверждённых consumers:

- partial `resources/views/theme/viar/partials/google_reviews_section.blade.php`;
- include на index, canvas и reviews pages;
- `app/Http/Controllers/PageController.php:387` — label HTML sitemap.

Секция имеет отдельный enable flag. CN/JP public routing не подтверждён, поэтому эти два файла сохраняются dormant. Наличие consumer группы не является разрешением автоматически включать `cn|jp` как публичные locale.

## 3. Решения, которые должен подписать владелец

| Decision set | Строк | Варианты | Safe default |
|---|---:|---|---|
| DB `header.top_sale` | 8 | production wins / local wins / manual translation | production live value |
| PHP `header.top_sale` | 8 | production wins / local wins / manual translation | production live value |
| `orders.not_specified` | 8 | retain / archive / retire after usage proof | retain dormant |
| RU `orders.user_status.print_text` | 1 | production wins / local wins / manual RU copy | production live value |
| CN/JP `google_reviews.php` | 2 | retain dormant / activate by separate locale decision / retire after proof | retain dormant |

DB и PHP строки `header.top_sale` подписываются раздельно как источники, но выбранное пользовательское содержание должно быть согласованным. Нельзя утвердить DB value и затем позволить publisher перезаписать PHP-файл другой редакцией.

## 4. Минимальный staging dataset

Staging должен содержать обезличенные или synthetic fixtures:

1. representative public URL для каждого `de|ee|en|et|lt|lv|pl|ru`;
2. `delivery_page` для тех же locale;
3. RU customer и собственный `in_production` order с painter assignment;
4. RU `in_production` order без painter assignment;
5. RU orders со статусами `watching|pegging|sended|completed`;
6. empty/fallback account field fixture;
7. controlled Google Reviews API success, empty и failure responses;
8. versioned current/candidate/previous translation artifacts;
9. deny-by-default тестовые роли для Translation Manager и `AdminLocaleController`;
10. manifest/hash baseline до активации.

Реальные production заказы, персональные данные или прямой live publish для UAT не требуются.

## 5. UAT gates

Полный протокол содержит 14 tests. Критические stop/no-go:

- на экране виден raw key или fallback не согласован;
- `header.top_sale` отличается между общим и delivery layout;
- `print_text` показывается без painter assignment или при другом status;
- включение candidate artifact активирует `cn|jp` public routing;
- Translation Manager/AdminLocale может перезаписать candidate без approved receipt;
- lint/manifest теряет locale/file/key или меняет один из семи semantic-equal `mail.php`;
- rollback не возвращает предыдущие hashes атомарно;
- UAT использует production writer или изменяет live перевод.

## 6. Порядок выполнения

1. Content owner заполняет 27 dispositions и указывает approved source/text owner.
2. Dev собирает candidate artifact из подписанной матрицы, не из live editor.
3. DevOps запускает lint/manifest и сохраняет artifact ID/hash.
4. QA выполняет I18N-UAT-001…014; Content/SEO подтверждают тексты и locale behavior.
5. При PASS выполняется rehearsal activation + rollback на staging.
6. Перед production wave снимается свежий read-only checkpoint, так как production продолжает принимать изменения.
7. Применяется только новый delta между checkpoint и подписанным artifact; unexplained drift возвращает gate в no-go.

## 7. Текущий вердикт

Все 27 owner decisions получены: production wins для 16 DB/PHP значений `header.top_sale` и RU `orders.user_status.print_text`; восемь `orders.not_specified` сохраняются dormant; CN/JP `google_reviews.php` не активируются и удерживаются до usage-proof archive/retire gate. Пакет staging UAT готов, но ещё не выполнен. Production-команды сейчас не нужны; команда может готовить isolated staging fixtures, candidate artifact и versioned publisher. До PASS по 14-case UAT, lint/manifest и rollback rehearsal live Translation Manager publish, `AdminLocaleController` write, copy/import/regeneration и target translation writes остаются запрещены.
