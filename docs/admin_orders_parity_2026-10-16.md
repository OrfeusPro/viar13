# ADM-AUD-13 — полная сверка «Заказов»

Статус: **IN PROGRESS**. Это исходная инвентаризация, полная приёмка не выполнена.

## Источники

- Original: `C:/OSPanel/domains/asoft/viar/resources/views/vendor/voyager/orders.blade.php`, `order.blade.php`, `order_create.blade.php`, `orders/*.blade.php`.
- Original: `app/Http/Controllers/Admin/OrdersController.php`, `routes/admin.php` того же read-only репозитория.
- New: `app/Filament/Resources/Orders/OrdersResource.php`, `Pages/*`, `Schemas/*`, `Tables/OrdersTable.php`, `Tables/Order*Actions.php`.
- New: `app/Services/Admin/UpdateOrderService.php` и отдельные Order*Service.

## Матрица первичной сверки кода

| Подзадача | Original | New / результат | Следующий шаг |
| --- | --- | --- | --- |
| ADM-AUD-13A — список | Динамические order_columns, номер/оплата/пользователь/получатель/товар/комментарии, художник, статусы и действия | Специализированные ViewColumn и actions существуют; присутствие не доказывает полную совместимость | Сопоставить каждый partial, правила отображения и данные по одному заказу; затем desktop/mobile |
| ADM-AUD-13B — фильтры/вкладки | order_id, payment request, customer, phone, price, payment status, source, category, country, painter, manager, size, start/end dates; специальные выборки | OrdersTable содержит соответствующие filter names; ListOrders содержит active/completed/unpaid/production/printing/express/sent_today/sent/new_recently/all и legacy rotation | Сравнить predicates, границы дат, пустые значения, combined filters и сортировку; прежние rotation tests не заменяют весь список |
| ADM-AUD-13C — редактирование | `order.blade.php` + update_admin_order/update_order_item_price/add_order_item/add_client_images: получатель/доставка, скидки, фирма, параметры, позиции/фото и информация | **Частично.** Перенесены перечисленные поля, отдельные update/add/attach actions, информационные блоки. Изолированные schema/service/upload/permission tests прошли; браузер не проверен | Далее остальные edit media/artist controls и PDF/file side effects; полный EditRecord, реальные file chooser/лимиты web PHP и desktop/mobile требуют приёмки |
| ADM-AUD-13D — создание | order_create и create_admin_order, ручной состав заказа | Отдельный AdminOrderCreateForm/AdminOrderCreationService, поддержка from_order_id и проверки прав имеются | Сопоставить default/options/доставку/скидки/товары/уведомления и результат каждого поддержанного типа |
| ADM-AUD-13E — номер/оплата/PDF/доставка | Original partials id/payment/invoice и специализированные endpoints | В OrdersTable зарегистрированы номер, payment request, invoice/company, Venipak create/print, email/review | Составить action-by-action matrix, отдельно UI/validation/permission/service/provider; внешних отправок не выполнять |
| ADM-AUD-13F — чаты/художник/жизненный цикл | Client/admin/SA/painter streams, assignments, statuses/sketches | Выделенные OrderClientChatActions/OrderPainterChatActions/OrderSaChatActions/OrderArtistActions/OrderLifecycleActions | Проверить каждый action/attachment/read-counter/status against original и существующие tests; не объявлять standalone SA audit полной проверкой Orders |
| ADM-AUD-13G — права/дизайн | Original gates и desktop layout | Policy/resource/action gates и новые Blade partials есть | Проверить matrix roles/revoked rights; визуальная приёмка не выполнена, browser control был отключён предыдущим шагом |

## Условия проверки

Рабочие заказы/изображения/права не менять. Запись — только отдельная SQLite/MariaDB fixture и fake storage. Реальные письма, платежи, Venipak и сообщения провайдерам не запускать. Каждый кодовый пропуск подтверждать исходным контрактом; каждый визуальный вывод — снимком реального UI. Возможности, которых в original нет, не добавлять под видом переноса.

## Текущее действие

Продолжить ADM-AUD-13C: сопоставить оставшиеся панели фото/набросков/картин и действия original edit с существующими adapters; затем PDF/file side effects и полная визуальная приёмка. Отдельно проверить реальный upload через Chrome/OpenServer, когда управление браузером доступно. Полная приёмка Orders остаётся открытой.

## ADM-AUD-13C — получатель и доставка, 2026-10-16

Перенесены поля имени/фамилии/email, двух телефонов, комментария, способа/страны/города/адреса/индекса/желаемой даты/цены доставки и выбора мастерской либо города доставки.

- Источники: original `order.blade.php:282–319,521–635,1450–1568`, `OrdersController::update_admin_order:1054+`.
- Справочники реальные: `country_tels`, `delivery_pickup_at_viar_workshop`, `a_delivery_towns`; options с RU-переводами, фильтрацией страны, is_show/sort для мастерских. Server mapping сохраняет базовое название/код справочника, как original.
- UI обновляет адрес/город при смене способа, страны и пункта. Поддержаны обе исторические записи pickup_Daugavplis/pickup_Daugavpils, значения ID1/2 из original. Для старых заказов без ID пункт определяется по существующему адресу/городу; при отсутствии селектора сервер сохраняет существующий ID/код в соответствующей ветке original.
- Телефоны очищаются от пробелов/скобок/дефисов; пустой телефон получателя использует телефон заказчика. Portrait-calculator content используется как fallback email/телефона. Поле имеет телефонную клавиатуру без несовместимого автоматического regex Filament.
- `delivery_edit` — только state формы, в БД сохраняется прежний `delivery` JSON. Разрешённые ключи объединяются с последним JSON под row lock; неизвестные extensions, items и пути файлов остаются. Старые callers без блока доставки сохраняют JSON побайтно. Неразбираемый JSON не перезаписывается; ошибки полей/справочника откатывают весь update.
- Флаг manual express сохраняется из delivery/items по original контракту. Общий новый переключатель в форму не добавлен: в original UI экспресс относится к позиции; перенос редактора позиций ещё предстоит.
- Общий комментарий синхронизируется в delivery.comment и orders.comment, включая явную очистку. Это исправляет original truthy-only сохранение, не позволяющее очистить комментарий.

Проверено: `OrderDeliveryFormTest` + `UpdateOrderServiceTest`, **14 tests / 120 assertions PASS**, SQLite `:memory:`, 1.892s/84MB. Schema harness использует настоящий OrderDeliveryForm: выборы/фильтрация/переключения, getState → UpdateOrderService → refill из сохранённой записи. Проверены обе ветки телефонов, legacy aliases/ID/code, RU labels, отмена неверного email/даты/цены/типа/ID, сохранность items/extensions/file paths и merge со свежим JSON. Первое выполнение roundtrip выявило несовместимый regex телефона; исправление подтверждено повторным запуском. Pint по семи затронутым PHP-файлам выполнен.

Не проверено: настоящий браузерный EditOrders desktop/mobile и права при submit; управление браузером ранее было отключено. Ни рабочие заказы/изображения, ни внешние сервисы не затрагивались. Original переименование uploads и регенерация PDF при общем сохранении здесь не подключены: требуют отдельной сверки и изолированных проверок, особенно с запретом менять рабочие пути изображений. ADM-AUD-13C и вся ADM-AUD-13 остаются IN PROGRESS.

## ADM-AUD-13C — скидки, фирма и параметры, 2026-10-16

Перенесён `OrderDetailsForm`: параметры и юридическая информация включены в OrdersForm. Менеджеры — реальные users с role_id=4, nick/email, плюс «Админ»=0. Устранён прежний отказ `exists:users,id` для нулевого администратора.

- Категории используют тот же `IndexController::get_styles_for_quiz('ru')`, что original и уже существующая форма создания: gallery_items id_type=5 плюс виртуальные CanvasHeader=1/ACollageHead=2, с удалением HTML span в подписи. Переиспользован существующий styleOptions; нового статического каталога нет. Сервер проверяет integer>=0, не ошибочный exists:gallery_items для виртуальных ID.
- Канал продаж из `a_order_from`, виден только если уже указан у заказа, как в original. Для переданного значения проверяется существование; не переданное поле сохраняется.
- `sale_eur`/`sale_percent` сохраняются одновременно. В edit нет правила взаимоисключения из create и нет автоматического изменения price/sale_price: original updateOrder просто обновляет колонки, а DynamicPDF отдельно вычитает EUR, потом процент. Это предотвращает двойное применение скидок в PDF.
- `approved_date` — текст, первоначально из existing либо created_at в d.m.Y H:i. Пустое поле не очищает прошлую дату, как original; смешанные исторические форматы не конвертируются.
- `ur_name` переводит состояние UI boolean в original on/empty; семь ur_* полей редактируются независимо от флага и сохраняются в прежних колонках. Явное очищение сохраняется как empty string. Старые callers без этих ключей сохраняют реквизиты, дату, категорию, канал и скидки.
- Изолированные service tests покрывают все семь реквизитов, оба discount значения без пересчёта base price, нулевого менеджера, ошибки/атомарность и preservation старых callers. Form harness использует реальные поля, настоящий mutateFormDataBeforeFill и handleRecordUpdate из EditOrders: заполнение → выборы/изменение → save → новая форма из БД; отдельно проверены скрытый канал, дата и сохранность delivery/items/image paths.

Предварительные проверки: UpdateOrderServiceTest+OrderDeliveryFormTest **18tests171assertionsPASS** (2.238s84MB); новый OrderDetailsFormTest **2tests37assertionsPASS** (1.053s76MB). Pint по шести затронутым PHP-файлам выполнен. Это isolated evidence, не визуальная приёмка настоящего браузера и не проверка полного EditRecord permission flow.

Остаётся: товарные позиции/информационные блоки, PDF/file side effects и полная визуальная/permission приёмка Orders; ADM-AUD-13C остаётся IN PROGRESS. Миграций, изменений рабочих заказов/изображений и реальных отправок нет.

## ADM-AUD-13C — существующие позиции и информационные блоки, 2026-10-16

Источники: original `OrdersController::update_order_item_price:929–1052`, `update_order_price:1289+`, `order.blade.php:1018–1250`, `resolveClientLocale:193+`, а также прежний перенесённый OrderItemPresentationService.

### Реализация

- OrderItemsForm: inline карточки существующих позиций, название, size_name/sizeId, цена, комментарий, manual canvas/gift/decoration/orientation/baget/express. Кнопка «Обновить позицию» сохраняет только выбранную карточку, как отдельная исходная форма. Нового удаления/перетаскивания позиций здесь нет; кнопка добавления и attachments ещё не перенесены.
- EditOrders заполняет items_edit через OrderItemEditor, сохраняя реальный numeric index (включая разреженные). Начальные значения повторяют original fallback из boxIds/compl_id, canvasId/holst_id, decoration IDs/L/P codes, formId/forma_id/form_id, ram_id и express terms. Числовой sizeId приводится к строке ввода. Служебные JSON ключи не становятся карточками.
- OrderItemEditor обновляет name/price/sumPrice и три formatted price, непустые размеры/show.size, trim comment, manual codes. Existing presentation service заполняет локализованные show/terms. Locale — delivery country (включая ET→ee/GB→en), затем preferredLocale клиента, затем ru.
- Пересчитываются items.total_terms_price, delivery.is_manual_express и orders.price/sale_price. sale_eur/sale_percent и другие order fields не меняются; остальные корневые JSON totals остаются как в original update. Числовое суммирование исправляет original ошибку floatval(number_format) для сумм от1000 с разделителем тысяч; тест1234.567→1234.57, total1254.57.
- Транзакция/row lock и fingerprint выбранной позиции защищают от перезаписи изменённой карточки; обновление другой карточки не теряется и участвует в свежем total. Неизвестные JSON keys, другие позиции, фотографии/пути сохраняются. Повреждённые items/delivery не перезаписываются. File rename/PDF generation не подключены, рабочие фото не менялись.
- Общий submit не включает items_edit и не сохраняет чужие черновики; его валидация не блокируется неверным черновиком позиции. После успешного item action в основной форме обновляются price/sale_price, чтобы общий Save не вернул старую сумму. Черновики других позиций остаются. Ошибки конкретной позиции имеют полный путь поля.
- Серверный action требует update policy/edit_orders, включая проверку внутри callback. Проверены скрытый action reader, попытка прямого mountAction и отзыв permission у уже открытой формы.
- Превью orig_images —96×96, object-fit:contain, ссылки безопасно экранированы через прежний image helper; пути не записываются из state.

### Информационные блоки

Read-only all_sales: facebook_sale, used_bonuses, used_coupon, used_coupon_30_40, dates_sale с прежними единицами/да/нет. Информация content показывается для is_admin_order=2 с переносами строк и экранированием HTML. Original textarea content_data есть в Blade, но update_admin_order не читает его и отдельного обработчика по поиску original controller/public JS не найдено. Новый UI показывает эти данные для чтения, не обещая несуществующее сохранение.

### Evidence и остаток

После Pint итог **30tests320assertionsPASS**,3.235s90MB: OrderItemEditorTest+OrderDetailsFormTest+OrderDeliveryFormTest+UpdateOrderServiceTest. JUnit `storage/app/audit/orders-edit-20261016.xml` обновлён. SQLite :memory:, реальные presentation/catalog reads на fixture, Mail fake и HTTP stray blocked. Проверены schema item action, основной getState, сумма и соседние черновики, original поля/коды/terms/локализация, legacy defaults/разреженные индексы, отсутствие записи paths, invalid/stale/broken JSON rollback, свежая соседняя позиция, reader/прямой вызов/revocation, безопасный информационный render. Предварительный gate28tests306checksPASS; дополнительная проверка разделения ошибок показала, что старый error bag позиции остаётся видимым после основного getState, но submit выполняется и исключает drafts — это проверено sentinel state.

Не закрыты: add_order_item, add_client_images и остальные original media controls в edit; original rename/PDF side effects; настоящий browser EditOrders/полный page permission flow/desktop/mobile. Проверки существующего read/action набросков и картин требуют отдельной связи с новой edit page, присутствие row actions полной приёмкой не считается. ADM-AUD-13C и вся13 IN PROGRESS; рабочих DB/storage/отправок не было, коммита нет.

## ADM-AUD-13C — добавление позиции и файлов, 2026-10-16

Источники: original `OrdersController::add_order_item:2537+`, `add_client_images:1349+`, `order.blade.php:1230–1250,1268–1345`. Add и attach — самостоятельные действия, не общий submit.

### Перенос

- Кнопка «Добавить товар» открывает форму с названием/ценой/размером/сроком/комментарием, исходными manual options/defaults и картинками. Новый numeric index добавляется после существующих разреженных индексов; старые позиции и unknown metadata сохраняются. Цена округляется до2, price/sale_price пересчитываются по позициям, discount columns не изменяются. Root saved_price/totalPrice/formatedTotalPrice увеличиваются по original контракту, включая нулевое начало отсутствующего aggregate. При express=0 delivery JSON не перезаписывается; при express=1 его flag устанавливается.
- Выбранный B0/B1/B2 сохраняется явно; original add UI имеет manual_baget_code select, но обработчик читал только несуществующий manual_baget checkbox. Эта ошибка исправлена, согласованно с уже перенесённым item update.
- Кнопка «Добавить фото» у каждой карточки принимает только index/fingerprint/files и дописывает orig_images именно этой позиции. Старые размеры/цена/название/комментарий/скидки/доставка не обновляются. Черновик карточки остаётся в форме, после upload обновляется fingerprint, затем обычное «Обновить позицию» работает. Неверный index не переводится молча на первую позицию, как исходный fallback: возвращается ошибка, чтобы не прикрепить файл к чужой карточке.
- Общие production fields вынесены в OrderItemsForm::productionFields для update/add; actions с policy edit_orders и повторной проверкой внутри callback. Add обновляет parent totals и добавляет карточку без потери других drafts. Upload modal показывает stale/validation errors у поля files.
- OrderItemUploads принимает UploadedFile, пакет<=30, каждый<=102400KB; проверяются suffix и определённый сервером MIME. New-item picker принимает перечисленные raster/HEIC formats; attach дополнительно PDF/PSD/FIG. Исполняемые расширения и HTML под .jpg отклоняются. SVG и произвольные image/* не разрешены этим whitelist; реальный PSD/FIG/HEIC chooser/format UAT пока не выполнен.
- Только новые файлы сохраняются на прежний диск uploads в orders/, с существующим Orders::generateImageName и collision-safe порядковым suffix. Существующие файлы/ссылки/bytes не переименовываются и не заменяются. Payload не может передать готовый путь вместо UploadedFile. DB transaction/row lock + компенсационная очистка tracked новых путей при исключении, частичной записи, отказе DB и model veto. Save=false теперь считается ошибкой в item add/attach/update и общем UpdateOrderService.

### Временная загрузка

Найдено несоответствие: UI (включая прежний AdminOrderCreateForm) допускает100MiB, Livewire без config/livewire.php использует временный лимит12MiB. Новый config задаёт global temporary-upload rules100MiB, сохраняя vendor defaults disk/directory, throttle60/min, preview_mimes,5min timeout и cleanup. Индивидуальные ограничения полей/сервисов продолжают действовать. Это изменение общего временного транспорта; PHP/OpenServer upload_max_filesize/post_max_size и реальная передача большого файла не проверены и здесь не изменялись.

### Evidence

Итог **43tests447assertionsPASS**,6.173s94MB после Pint8files: OrderItemEditorTest+OrderDetailsFormTest+OrderDeliveryFormTest+UpdateOrderServiceTest. JUnit `storage/app/audit/orders-edit-20261016.xml` обновлён. Только SQLite :memory: + fake uploads/local/Livewire tmp-for-tests, Mail fake/HTTP stray blocked. Предварительный21tests228checksPASS, общая42tests438checksPASS до transport gate.

Покрыто: production Livewire add с временным PNG→постоянный файл→новая карточка/totals, attach с PNG→следующий item update, сохранность соседних drafts; реальные business naming/collision и byte preservation старого файла; PDF по определённому MIME (хранение, не decode), image-only add отклоняет PDF; подмена .jpg проверена настоящим UploadedFile/finfo (Laravel FakeFile сам подставляет MIME по имени); oversized file metadata/bad batch; second-file partial write cleanup; DB exception/model veto cleanup; reader/forged add mount/revocation в открытом upload modal; stale error у files; temp transport rules20MiB accept/>100MiB reject. Проверки больших размеров используют metadata fake, фактический100MiB transfer не выполнялся.

Осталось: реальные chooser/drop/upload progress/cancel/PSD-FIG-HEIC и PHP limits в браузере; остальные original edit image/artist panels/actions; PDF/general save side effects; полноценный EditRecord page/permission/desktop/mobile gate. Rename existing working photos запрещён пользовательским ограничением; любые будущие проверки только fake/isolated storage. ADM-AUD-13C/13 остаются IN PROGRESS, рабочих DB/images/providers не затрагивали, коммита нет.
