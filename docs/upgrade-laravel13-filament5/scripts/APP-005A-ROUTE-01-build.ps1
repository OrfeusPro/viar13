param(
    [string]$LegacyRoot = 'C:\OSPanel\domains\asoft\viar',
    [string]$TargetRoot = 'G:\OSPanel\home\viar_filament',
    [string]$TargetPhp = 'G:\OSPanel\modules\PHP-8.3\PHP\php.exe',
    [string]$Output = 'C:\OSPanel\domains\asoft\viar\docs\upgrade-laravel13-filament5\appendix-public-route-disposition.csv'
)

$ErrorActionPreference = 'Stop'

function Normalize-Methods([string]$Methods) {
    return (($Methods -split '\|') |
        Where-Object { $_ -notin @('HEAD', 'OPTIONS') } |
        Sort-Object -Unique) -join '|'
}

function Normalize-TargetUri([string]$Uri) {
    if ($Uri -eq '{locale}') {
        return '/'
    }

    return $Uri -replace '^\{locale\}/', ''
}

$script:SourceCache = @{}

function Get-CodeEvidence($Route) {
    $relativePath = $Route.source_file
    if ([string]::IsNullOrWhiteSpace($relativePath) -or
        $relativePath -eq 'routes/redirect.php' -or
        $relativePath -like 'vendor/*') {
        return @('', '')
    }

    $absolutePath = Join-Path $LegacyRoot ($relativePath -replace '/', '\')
    if (-not (Test-Path -LiteralPath $absolutePath)) {
        return @('', 'SOURCE_MISSING')
    }

    if (-not $script:SourceCache.ContainsKey($absolutePath)) {
        $script:SourceCache[$absolutePath] = @(Get-Content -LiteralPath $absolutePath)
    }

    $lines = $script:SourceCache[$absolutePath]
    $start = [Math]::Max(0, ([int]$Route.source_line) - 1)
    $end = [Math]::Min($lines.Count - 1, $start + 220)

    for ($index = $start + 1; $index -le $end; $index++) {
        if ($lines[$index] -match '^\s*(public|protected|private)\s+function\s+') {
            $end = $index - 1
            break
        }
    }

    $body = ($lines[$start..$end] -join "`n")
    $views = @([regex]::Matches($body, '(?i)(?:view|View::make)\s*\(\s*[''"]([^''"]+)[''"]') |
        ForEach-Object { $_.Groups[1].Value } |
        Sort-Object -Unique)

    $signalPatterns = [ordered]@{
        DB = '(?i)\bDB::|->(insert|insertGetId|update|delete)\s*\('
        MODEL_WRITE = '(?i)::(create|updateOrCreate|firstOrCreate)\s*\(|->save\s*\('
        FILE = '(?i)\b(Storage::|File::)|->move\s*\(|\b(unlink|rename|copy)\s*\('
        MAIL = '(?i)\bMail::|->send\s*\('
        SESSION = '(?i)\b(session|Session)::|->session\s*\('
        EXTERNAL = '(?i)\b(Http::|curl_|WebToPay|PayPal|Synvolve|Socialite|Venipak)'
        QUEUE = '(?i)\b(dispatch|queue|Artisan::)\s*\('
        REDIRECT = '(?i)\bredirect\s*\(|\bRedirect::'
    }

    $signals = @()
    foreach ($entry in $signalPatterns.GetEnumerator()) {
        if ($body -match $entry.Value) {
            $signals += $entry.Key
        }
    }

    return @(($views -join '|'), ($signals -join '|'))
}

function Get-Classification($Route) {
    $uri = $Route.uri
    $controller = $Route.controller -replace '@.*$', ''
    $handler = $Route.handler
    $source = $Route.source_file

    if ($source -eq 'routes/redirect.php') {
        return @('redirects', 'READ_REDIRECT', 'SECURITY_REPLACE', 'SEO-003+SEO-004')
    }

    if ($uri -like '_debugbar/*') {
        return @('developer endpoints', 'DEV_READ_OR_WRITE', 'SECURITY_REPLACE', 'SEO-001')
    }

    if ($uri -eq 'arrilot/load-widget') {
        return @('runtime widgets', 'READ_DB', 'MISSING_TARGET', 'APP-005B-PAGES-01')
    }

    if ($uri -match '^(storage|uploads|orders)/\{path\}$' -or $uri -eq 'image/{filename}') {
        return @('public file delivery', 'READ_FILE', 'SECURITY_REPLACE', 'APP-001')
    }

    if ($uri -match '^mail/(1|2|3|4|5|6|7)$') {
        return @('mail previews', 'READ_PRIVATE_DATA', 'SECURITY_REPLACE', 'SEC-010')
    }

    if ($uri -in @('translate_item', 'set_meta')) {
        return @('public catalogue translation maintenance', 'PUBLIC_GET_DB_EXTERNAL_WRITE', 'SECURITY_REPLACE', 'SEO-001+CUT-009')
    }

    if ($uri -eq 'new/set_all_painter_images') {
        return @('account bulk maintenance', 'PUBLIC_GET_DB_WRITE', 'SECURITY_REPLACE', 'SEC-009+APP-001')
    }

    if ($Route.methods -eq 'GET' -and $uri -in @(
        'basket/submitbonuses',
        'basket/thanks',
        'cart/clear_coupon',
        'save_base64_image2',
        'save_order_and_pay',
        'orders/remove_painter_sketch_image',
        'user/{user_id}/approve_checkout/{order_id}'
    )) {
        return @('unsafe checkout GET mutation', 'PUBLIC_GET_MIXED_WRITE', 'SECURITY_REPLACE', 'APP-005B-CHECKOUT-01+SEC-009')
    }

    $done = @{
        'GET|/' = 'APP-005A'
        'GET|condition' = 'APP-005A'
        'GET|robots.txt' = 'APP-005A'
        'GET|thanks' = 'APP-005A-FORM-01+APP-005A-FORM-02'
        'POST|custom_login_ajax' = 'APP-005A'
        'POST|custom_register_ajax' = 'APP-005A'
        'GET|password/reset/{token}' = 'APP-005A'
        'POST|password/reset' = 'APP-005A'
        'POST|user/forget_email' = 'APP-005A'
        'POST|all_styles_form' = 'APP-005A-FORM-01'
        'POST|send_photo_form' = 'APP-005A-FORM-02'
        'POST|user/send_photo_form' = 'APP-005A-FORM-02'
    }

    $doneKey = "$(Normalize-Methods $Route.methods)|$uri"
    if ($done.ContainsKey($doneKey)) {
        $effect = if ($Route.methods -eq 'GET') { 'READ_DB' } else { 'WRITE_MIXED_GATED' }
        return @('implemented foundation', $effect, 'PARITY_DONE', $done[$doneKey])
    }

    if ($controller -like 'App\Http\Controllers\Auth\*') {
        return @('standard auth', 'SESSION_OR_DB_WRITE', 'SHELL_ONLY', 'AUTH-001')
    }

    if ($controller -eq 'App\Http\Controllers\SocialController') {
        return @('social auth', 'EXTERNAL_AUTH_SESSION', 'MISSING_TARGET', 'AUTH-002')
    }

    if ($controller -like 'App\Http\Controllers\Account*') {
        if ($handler -match 'chat|comment|message|painter.*image|ImageStatus|set_all_painter_images') {
            return @('account chat and artwork', 'WRITE_DB_OR_FILE', 'MISSING_TARGET', 'CHAT-001+CHAT-002+APP-001')
        }
        if ($handler -match 'payment') {
            return @('account payment', 'PAYMENT_WRITE', 'MISSING_TARGET', 'CUT-004+INT-002+INT-003')
        }
        return @('account and profile', 'READ_OR_WRITE_DB', 'MISSING_TARGET', 'AUTH-001+SEC-009')
    }

    if ($controller -eq 'App\Http\Controllers\BasketController' -or
        $controller -eq 'App\Http\Controllers\OrdersController' -or
        $controller -eq 'App\Http\Controllers\AbandonedCartController') {
        return @('basket checkout and order', 'SESSION_DB_FILE_MAIL_WRITE', 'MISSING_TARGET', 'APP-005B-CHECKOUT-01')
    }

    if ($controller -eq 'App\Http\Controllers\GiftcardController') {
        return @('gift card and PDF', 'READ_OR_WRITE_FILE', 'MISSING_TARGET', 'APP-003+APP-005B-CHECKOUT-01')
    }

    if ($controller -eq 'App\Http\Controllers\GalleryController') {
        if ($uri -in @('set_sizes', 'set_genre', 'set_style')) {
            return @('catalogue maintenance', 'PUBLIC_GET_DB_WRITE', 'SECURITY_REPLACE', 'SEC-011')
        }
        return @('catalogue and gallery', 'READ_DB', 'MISSING_TARGET', 'CAT-001')
    }

    if ($controller -match 'PortraitPageController|Pages\\(SharjController|PagePortraitOilController|PagePortraitRoyalController|SimpsonsController|ModulegeneratorController|SizespricesController)' -or
        $uri -match 'constructor|portrait|simpson|modulegenerator|sizesprices') {
        return @('product generators', 'READ_DB_SESSION', 'MISSING_TARGET', 'CAT-002')
    }

    if ($controller -match 'Payment\\|Libwebtopay\\PayseraController') {
        if ($handler -match 'PayPal') {
            return @('PayPal', 'PAYMENT_CALLBACK_WRITE', 'MISSING_TARGET', 'INT-003+CUT-004')
        }
        return @('Paysera', 'PAYMENT_CALLBACK_WRITE', 'MISSING_TARGET', 'INT-002+CUT-004')
    }

    if ($controller -eq 'App\Http\Controllers\Admin\Api\VinepakApiController') {
        return @('delivery lookup', 'EXTERNAL_DELIVERY_READ', 'MISSING_TARGET', 'INT-006')
    }

    if ($controller -eq 'App\Http\Controllers\MailController') {
        return @('mail preview and recovery', 'READ_OR_DB_MAIL_WRITE', 'SECURITY_REPLACE', 'SEC-010+MAIL-001')
    }

    if ($controller -eq 'App\Http\Controllers\SitemapController' -or $controller -eq 'App\Http\Controllers\RobotsController') {
        return @('SEO feeds', 'READ_DB', 'MISSING_TARGET', 'SEO-005+SEO-007')
    }

    if ($controller -eq 'App\Http\Controllers\Pages\ReviewController') {
        $effect = if ($Route.methods -eq 'POST') { 'WRITE_DB_FILE' } else { 'READ_DB' }
        return @('reviews', $effect, 'MISSING_TARGET', 'SEO-006')
    }

    if ($controller -eq 'App\Http\Controllers\UserManageController') {
        if ($uri -eq 'user/{id}/unsubscribe') {
            return @('unsubscribe', 'PUBLIC_GET_DB_WRITE', 'SECURITY_REPLACE', 'MAIL-002+SEC-009')
        }
        if ($uri -eq 'user_send_rev/{locale}') {
            return @('reviews', 'WRITE_DB_FILE', 'MISSING_TARGET', 'SEO-006')
        }
        return @('auxiliary public forms', 'DB_FILE_MAIL_WRITE', 'MISSING_TARGET', 'APP-005B-FORM-03')
    }

    if ($controller -eq 'App\Http\Controllers\Admin\ImageGenController') {
        return @('public image generation utility', 'PUBLIC_EXTERNAL_FILE_WRITE', 'SECURITY_REPLACE', 'SEO-001+APP-001')
    }

    if ($controller -match 'StaticPagesController|PageController|AdvertisingController|BlogController|IndexController|MyController|Pages\\FaqController|Pages\\ConditionController') {
        if ($Route.methods -ne 'GET') {
            return @('content actions', 'DB_OR_MAIL_WRITE', 'MISSING_TARGET', 'APP-005B-FORM-03')
        }
        return @('public content pages', 'READ_DB', 'MISSING_TARGET', 'APP-005B-PAGES-01')
    }

    return @('unclassified public route', 'UNKNOWN', 'MISSING_TARGET', 'APP-005A-ROUTE-01')
}

$legacyCsv = Join-Path $LegacyRoot 'docs\upgrade-laravel13-filament5\appendix-routes.csv'
$legacy = Import-Csv -LiteralPath $legacyCsv |
    Where-Object { $_.uri -notlike 'admin*' -and $_.uri -notlike 'api/*' }

$targetJson = & $TargetPhp (Join-Path $TargetRoot 'artisan') route:list --json
if ($LASTEXITCODE -ne 0) {
    throw 'Target route:list failed.'
}
$target = $targetJson | ConvertFrom-Json

$targetIndex = @{}
foreach ($route in $target) {
    $normalizedUri = Normalize-TargetUri $route.uri
    $normalizedMethods = Normalize-Methods $route.method
    $key = "$normalizedMethods|$normalizedUri"
    if (-not $targetIndex.ContainsKey($key)) {
        $targetIndex[$key] = @()
    }
    $targetIndex[$key] += "$($route.method) $($route.uri) [$($route.name)]"
}

$legacyIndex = @{}
foreach ($route in $legacy) {
    $normalizedMethods = Normalize-Methods $route.methods
    $key = "$normalizedMethods|$($route.uri)"
    if (-not $legacyIndex.ContainsKey($key)) {
        $legacyIndex[$key] = @()
    }
    $legacyIndex[$key] += "$($route.methods) $($route.uri) [$($route.name)]"
}

$rows = foreach ($route in $legacy) {
    $normalizedMethods = Normalize-Methods $route.methods
    $key = "$normalizedMethods|$($route.uri)"
    $classification = Get-Classification $route
    $codeEvidence = Get-CodeEvidence $route
    $targetMatches = if ($targetIndex.ContainsKey($key)) {
        $targetIndex[$key] -join ' || '
    } else {
        ''
    }

    [pscustomobject][ordered]@{
        legacy_methods = $route.methods
        legacy_uri = $route.uri
        legacy_name = $route.name
        legacy_handler = $route.handler
        legacy_middleware = $route.middleware
        source_file = $route.source_file
        source_line = $route.source_line
        view_candidates = $codeEvidence[0]
        code_signals = $codeEvidence[1]
        flow = $classification[0]
        side_effect_class = $classification[1]
        target_match = $targetMatches
        disposition = $classification[2]
        task_id = $classification[3]
    }
}

$rows |
    Sort-Object legacy_uri, legacy_methods, legacy_name |
    Export-Csv -LiteralPath $Output -NoTypeInformation -Encoding UTF8

$targetOutput = Join-Path (Split-Path -Parent $Output) 'appendix-target-public-route-disposition.csv'
$targetRows = foreach ($route in $target) {
    $isPublic = $route.uri -notlike 'admin*' -and
        $route.uri -notlike 'api/*' -and
        $route.uri -notlike 'filament/*' -and
        $route.uri -notlike 'livewire-*' -and
        $route.uri -notlike 'storage/*' -and
        $route.uri -ne 'up'

    if (-not $isPublic) {
        continue
    }

    $normalizedUri = Normalize-TargetUri $route.uri
    $normalizedMethods = Normalize-Methods $route.method
    $key = "$normalizedMethods|$normalizedUri"
    $legacyMatches = if ($legacyIndex.ContainsKey($key)) {
        $legacyIndex[$key] -join ' || '
    } else {
        ''
    }
    $disposition = if ($legacyMatches) {
        'LEGACY_MAPPED'
    } elseif ($route.uri -eq 'ru' -or $route.uri -like 'ru/*') {
        'COMPATIBILITY_ALIAS'
    } else {
        'TARGET_ONLY_REVIEW'
    }

    [pscustomobject][ordered]@{
        target_methods = $route.method
        target_uri = $route.uri
        target_name = $route.name
        target_action = $route.action
        normalized_key = $key
        legacy_matches = $legacyMatches
        disposition = $disposition
    }
}

$targetRows |
    Sort-Object target_uri, target_methods, target_name |
    Export-Csv -LiteralPath $targetOutput -NoTypeInformation -Encoding UTF8

$callsiteOutput = Join-Path (Split-Path -Parent $Output) 'appendix-public-client-endpoint-calls.csv'
$legacyNameIndex = @{}
$legacyUriIndex = @{}
foreach ($route in $legacy) {
    if (-not [string]::IsNullOrWhiteSpace($route.name)) {
        $legacyNameIndex[$route.name] = $true
    }
    $legacyUriIndex[$route.uri.TrimStart('/')] = $true
}
$scanRoots = @(
    (Join-Path $LegacyRoot 'resources\views'),
    (Join-Path $LegacyRoot 'public\theme\viar\js')
)
$clientCallsites = @()

foreach ($file in Get-ChildItem -Path $scanRoots -Recurse -File |
    Where-Object {
        $_.Extension -in @('.php', '.js') -and
        $_.FullName -notmatch '\\resources\\views\\admin\\' -and
        $_.FullName -notmatch '\\resources\\views\\vendor\\' -and
        $_.Name -notlike '*.min.js'
    }) {
    $lines = @(Get-Content -LiteralPath $file.FullName)
    $relativeFile = $file.FullName.Substring($LegacyRoot.Length + 1).Replace('\', '/')

    for ($index = 0; $index -lt $lines.Count; $index++) {
        $line = $lines[$index]
        $kind = ''
        $method = ''
        $endpoint = ''

        if ($line -match '(?i)\$\.ajax\s*\(') {
            $kind = 'jquery_ajax'
            $windowEnd = [Math]::Min($lines.Count - 1, $index + 25)
            $window = $lines[$index..$windowEnd] -join "`n"
            if ($window -match '(?im)^\s*(?:type|method)\s*:\s*[''"]?([^,''"\r\n]+)') {
                $method = $Matches[1].Trim()
            }
            if ($window -match '(?im)^\s*url\s*:\s*([^,\r\n]+)') {
                $endpoint = $Matches[1].Trim()
            }
        } elseif ($line -match '(?i)\$\.(post|get)\s*\(\s*([^,\r\n]+)') {
            $kind = 'jquery_short'
            $method = $Matches[1].ToUpperInvariant()
            $endpoint = $Matches[2].Trim()
        } elseif ($line -match '(?i)\bfetch\s*\(\s*([^,\r\n]+)') {
            $kind = 'fetch'
            $endpoint = $Matches[1].Trim()
        } elseif ($line -match '(?i)\baxios\.(post|get|put|patch|delete)\s*\(\s*([^,\r\n]+)') {
            $kind = 'axios'
            $method = $Matches[1].ToUpperInvariant()
            $endpoint = $Matches[2].Trim()
        } elseif ($line -match '(?i)<form[^>]+\baction\s*=\s*(?<quote>[''"])(?<endpoint>.*?)\k<quote>') {
            $kind = 'form'
            $endpoint = $Matches['endpoint'].Trim()
            if ($line -match '(?i)\bmethod\s*=\s*[''"]([^''"]+)[''"]') {
                $method = $Matches[1].ToUpperInvariant()
            } else {
                $method = 'GET'
            }
        }

        if ($kind -ne '') {
            $resolvedRouteName = ''
            $resolvedLiteralUri = ''
            $legacyMatch = ''

            if ($endpoint -match '(?i)\broute\s*\(\s*[''"]([^''"]+)[''"]') {
                $resolvedRouteName = $Matches[1]
                $legacyMatch = if ($legacyNameIndex.ContainsKey($resolvedRouteName)) {
                    'ROUTE_NAME_MATCH'
                } else {
                    'ROUTE_NAME_UNRESOLVED'
                }
            } elseif ($endpoint -match '^[''"](/?[^''"`$?]+)(?:\?[^''"]*)?[''"]$') {
                $resolvedLiteralUri = $Matches[1].Trim('/')
                $legacyMatch = if ($legacyUriIndex.ContainsKey($resolvedLiteralUri)) {
                    'LITERAL_URI_MATCH'
                } else {
                    'LITERAL_URI_UNRESOLVED'
                }
            }

            $clientCallsites += [pscustomobject][ordered]@{
                file = $relativeFile
                line = $index + 1
                kind = $kind
                method = $method
                endpoint_expression = $endpoint
                resolved_route_name = $resolvedRouteName
                resolved_literal_uri = $resolvedLiteralUri
                legacy_match = $legacyMatch
            }
        }
    }
}

$clientCallsites |
    Sort-Object file, line, kind |
    Export-Csv -LiteralPath $callsiteOutput -NoTypeInformation -Encoding UTF8

$summary = $rows |
    Group-Object disposition |
    Sort-Object Name |
    ForEach-Object { [pscustomobject]@{ disposition = $_.Name; count = $_.Count } }

$taskSummary = $rows |
    Group-Object task_id |
    Sort-Object -Property @{ Expression = 'Count'; Descending = $true }, Name |
    ForEach-Object { [pscustomobject]@{ task_id = $_.Name; count = $_.Count } }

[pscustomobject]@{
    legacy_public_rows = $rows.Count
    target_registered_rows = $target.Count
    target_normalized_keys = $targetIndex.Count
    output = $Output
    sha256 = (Get-FileHash -LiteralPath $Output -Algorithm SHA256).Hash.ToLowerInvariant()
    target_public_rows = $targetRows.Count
    target_output = $targetOutput
    target_sha256 = (Get-FileHash -LiteralPath $targetOutput -Algorithm SHA256).Hash.ToLowerInvariant()
    client_callsites = $clientCallsites.Count
    client_callsite_output = $callsiteOutput
    client_callsite_sha256 = (Get-FileHash -LiteralPath $callsiteOutput -Algorithm SHA256).Hash.ToLowerInvariant()
    dispositions = $summary
    tasks = $taskSummary
} | ConvertTo-Json -Depth 5
