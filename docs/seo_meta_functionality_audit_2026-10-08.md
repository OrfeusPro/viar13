# ADM-AUD-02 — SEO Meta: сверка функционала, 2026-10-08

## Источники

- Оригинал: `app/Http/Controllers/Voyager/SeoMetaSuggestionController.php`, `SeoMetaGenerationController.php`, `resources/views/vendor/voyager/seo-meta-suggestions/browse.blade.php`.
- Новая панель: SeoMetaSuggestionResource / ListSeoMetaSuggestions / SeoMetaSummary, SeoMetaModerationService, GenerateSeoMetaSuggestion; BREAD `VoyagerBread::generateSeoMeta`.
- Общая сохранённая логика: SeoMetaScanCommand, SeoMetaContextResolver, SeoMetaGenerator, OpenAiSeoMetaClient, `config/seo_meta_generation.php`.
- Реальная БД: только чтение структуры всех настроенных targets. 19 моделей, обе SEO-колонки существуют у каждой. Языки ru/en/lv/ee/lt/pl/de. Evidence: `storage/app/testing/seo-target-audit-2026-10-08.json`.

## Сверка

| Сценарий оригинала | Результат |
| --- | --- |
| Сканировать все/одну модель, все/один язык, limit, only_empty, force | Сохранён общий CLI; форма Filament передаёт параметры. Default only_empty восстановлен false. Повтор/force/язык проверены тестом: без дублей и без изменения исходных meta. |
| Фильтры status/model/locale/URL/ошибка/пустые title/description/any/both | Присутствуют; статусные вкладки, поиск и статистика сохранены. |
| Поиск по названию сущности/модели/meta/keywords/error/URL | Добавлен пропущенный entity_label; остальные поля уже присутствовали. Проверено выборкой теста. |
| Порядок по обновлению, затем ID | Восстановлен default updated_at desc / id desc. |
| Открыть сущность и публичную страницу | Ссылки присутствуют. Ссылка редактирования проверяет BREAD edit permission. |
| Генерация и повторная генерация выбранного языка с keywords | Используется прежний генератор через Job; права, keywords, асинхронное обновление и отказ без источника/ошибка проверены. |
| Контекст, cache, ограничения длины, дневной лимит, fallback модели, запись модели/tokens/context/date/error | Общий генератор и клиент сохранены; service переносит результат и ошибку в те же поля. Реальный внешний запрос в этом аудите не выполнялся. |
| Редактирование и одобрение без применения к источнику | Сохранено, исправлен null/пустой ввод: очищенное предложение больше не подставляется заново из генерации. Два пустых meta не одобряются. |
| Применить только одобренное | Сохранены статус/права/транзакция/actor/date и запись в язык источника. Пустое одобренное поле не затирает исходное; current_meta теперь отражает фактическое значение источника. |
| Отклонить и сохранить reviewed_by/reviewed_at | Сохранено; после reject остаётся возможность новой генерации. |
| Keywords из строки при Apply/Reject | Пропуск исправлен: сохраняются с trim и валидацией до операции. Невалидный ввод не меняет keywords/status. |
| Bulk generate/approve/apply/reject | Сохранены штатные выбор/права/подтверждение/результат. Bulk approve теперь пропускает approved, как оригинал, и не заменяет ручную редакцию сгенерированным текстом. |
| Переводы и сохранность остальных данных | Apply пишет только согласованные meta текущего языка, BREAD генерация сохраняет meta для поддержанных языков. Проверены базовый язык, перевод и неизменность исходных полей до Apply. |
| Browse без edit | Нет пишущих действий; прямой вызов отклоняется тестом. |

## Проверки и границы результата

- `php -d memory_limit=512M vendor/bin/phpunit tests/Feature/Admin/VoyagerBreadTest.php --filter test_seo_ --colors=never`: **18 tests / 161 assertions PASS**.
- Добавлено 5 regression tests: пустые поля и snapshot; сохранение reviewed при bulk; keywords Apply/Reject и повтор после ошибки; поиск/default scan; повторный scan/force/locale.
- PHP lint трёх изменённых PHP-файлов и `git diff --check` PASS.
- Тесты: SQLite :memory:, Queue fake и mock генератора. Рабочая БД не изменялась, реальные запросы генерации/оплаты не выполнялись.
- Локальная queue.default = sync; наличие настроенного API key подтверждено без вывода значения. Это не подтверждает работоспособность ключа, доступность/стоимость внешней модели или production worker.
- Аудит кода и исправления завершены. **Осталось** отдельное сквозное испытание реального генератора и проверка конфигурации очереди на production. Полное внешнее UAT не считается DONE по результатам mock-тестов.
