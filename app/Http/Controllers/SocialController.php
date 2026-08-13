<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;
use Auth;

class SocialController extends Controller
{
    /** Маппинг локаль → страна по умолчанию */
    private const LOCALE_TO_COUNTRY = [
        'de' => 'DE',
        'ee' => 'EE',
        'et' => 'EE', // alias для эстонского
        'lt' => 'LT',
        'lv' => 'LV',
        'pl' => 'PL',
        'ru' => 'RU',
        'en' => 'LV', // при желании поменяй на GB/US
    ];

    /** Разрешённые локали */
    private const SUPPORTED_LOCALES = ['de','ee','en','et','lt','lv','pl','ru'];

    /** Дефолты, когда префикса языка нет */
    private const DEFAULT_LOCALE  = 'ru';
    private const DEFAULT_COUNTRY = 'RU';

    /**
     * Сохраняем intended-URL (куда вернуться) и извлекаем локаль/страну
     * из страницы, с которой пользователь начал OAuth.
     */
    protected function putIntended(Request $request): void
    {
        $fallback = route('home'); // локализованный home
        $return = $request->query('return', url()->previous());

        $forbidden = [
            route('google_redirect'), route('google_callback'),
            route('facebook_redirect'), route('facebook_callback'),
        ];

        // защита от плохих возвратных URL
        if (!$return || in_array($return, $forbidden, true)) {
            $return = $fallback;
        } elseif (preg_match('~^https?://~i', $return)) {
            if (strpos($return, url('/')) !== 0) {
                $return = $fallback;
            }
        }

        // 1) Куда вернуться после входа
        session(['url.intended' => $return]);

        // 2) Достаём локаль и страну из первого сегмента URL
        [$locale, $country] = $this->detectLocaleCountryFromUrl($return);

        // 3) Сохраняем контекст регистрации
        session([
            'signup.locale'  => $locale,
            'signup.country' => $country,
            'signup.page'    => $return,
            'signup.ua'      => $request->userAgent(),
            'signup.ip'      => $request->ip(),
        ]);
    }

    /**
     * Возвращает [locale, country] на основании первого сегмента пути URL.
     */
    private function detectLocaleCountryFromUrl(?string $url): array
    {
        if (!$url) {
            return [self::DEFAULT_LOCALE, self::DEFAULT_COUNTRY];
        }
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/'); // например "pl/some/page"
        $first = strtolower(strtok($path, '/')) ?: '';

        if (in_array($first, self::SUPPORTED_LOCALES, true)) {
            $country = self::LOCALE_TO_COUNTRY[$first] ?? self::DEFAULT_COUNTRY;
            return [$first, $country];
        }

        return [self::DEFAULT_LOCALE, self::DEFAULT_COUNTRY];
    }

    // -------------------- FACEBOOK --------------------

    public function facebook_redirect(Request $request)
    {
        $this->putIntended($request);
        // при необходимости можно указать ->scopes(['email'])
        return Socialite::driver('facebook')->redirect();
    }

    public function facebook_callback(Request $request)
    {
        try {
            // stateless чтобы не падать на state/сессии
            $social = Socialite::driver('facebook')->stateless()->user();

            Log::info('Facebook OAuth callback payload', [
                'id'    => $social->getId(),
                'email' => $social->getEmail(),
                'name'  => $social->getName(),
            ]);

            $locale  = session('signup.locale',  self::DEFAULT_LOCALE);
            $country = session('signup.country', self::DEFAULT_COUNTRY);
            $page    = session('signup.page',    route('home'));
            $ua      = session('signup.ua',      $request->userAgent());
            $ip      = session('signup.ip',      $request->ip());

            $email = $social->getEmail();
            if (!$email) {
                Log::warning('Facebook OAuth: email not returned');
                return redirect()->intended(route('home'))
                    ->with('oauth_error', 'Facebook не вернул email. Разрешите доступ к email и попробуйте ещё раз.');
            }

            $name = $social->getName(); // у FB чаще всего одно поле name
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'first_name'        => $name,
                    'password'          => Hash::make(Str::random(16)),
                    'email_verified_at' => now(),
                    'country'           => $country,
                    // ВАЖНО: settings коллекцией (или массивом при cast-е)
                    'settings'          => collect(['locale' => $locale]),
                    'registration_page' => $page,
                    'user_agent'        => $ua,
                    'last_ip'           => $ip,
                ]
            );

            // Мягкое обновление при повторном входе
            $user->country           = $country;
            $user->settings          = collect(['locale' => $locale]);
            $user->registration_page = $page;
            $user->user_agent        = $ua;
            $user->last_ip           = $ip;
            $user->save();

            Auth::login($user);
            return redirect()->intended(route('home'));
        } catch (Throwable $th) {
            Log::error('Facebook OAuth error', ['ex' => $th->getMessage()]);
            return redirect()->intended(route('home'))
                ->with('oauth_error', 'Ошибка входа через Facebook. Попробуйте ещё раз.');
        }
    }

    // -------------------- GOOGLE --------------------

    public function google_redirect(Request $request)
    {
        $this->putIntended($request);

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account']) // можно добавить 'consent'
            ->redirect();
    }

    public function google_callback(Request $request)
    {
        try {
            // stateless обязательно — меньше сюрпризов с state/samesite/cf
            $social = Socialite::driver('google')->stateless()->user();

            Log::info('Google OAuth callback payload', [
                'id'    => $social->getId(),
                'email' => $social->getEmail(),
                'name'  => $social->getName(),
                'raw'   => array_keys((array) $social->user), // только ключи
            ]);

            $locale  = session('signup.locale',  self::DEFAULT_LOCALE);
            $country = session('signup.country', self::DEFAULT_COUNTRY);
            $page    = session('signup.page',    route('home'));
            $ua      = session('signup.ua',      $request->userAgent());
            $ip      = session('signup.ip',      $request->ip());

            $email = $social->getEmail();
            if (!$email) {
                Log::warning('Google OAuth: email not returned');
                return redirect()->intended(route('home'))
                    ->with('oauth_error', 'Google не вернул email. Разрешите доступ к email и попробуйте ещё раз.');
            }

            $firstName = $social->user['given_name']  ?? null;
            $lastName  = $social->user['family_name'] ?? null;

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'first_name'        => $firstName,
                    'last_name'         => $lastName,
                    'password'          => Hash::make(Str::random(16)),
                    'email_verified_at' => now(),
                    'country'           => $country,
                    // ВАЖНО: settings коллекцией (или массивом при cast-е)
                    'settings'          => collect(['locale' => $locale]),
                    'registration_page' => $page,
                    'user_agent'        => $ua,
                    'last_ip'           => $ip,
                ]
            );

            // Мягкое обновление при повторном входе
            $user->country           = $country;
            $user->settings          = collect(['locale' => $locale]);
            $user->registration_page = $page;
            $user->user_agent        = $ua;
            $user->last_ip           = $ip;
            $user->save();

            Auth::login($user);
            return redirect()->intended(route('home'));
        } catch (Throwable $th) {
            Log::error('Google OAuth error', ['ex' => $th->getMessage()]);
            return redirect()->intended(route('home'))
                ->with('oauth_error', 'Ошибка входа через Google. Попробуйте ещё раз.');
        }
    }
}
