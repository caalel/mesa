<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['pt_BR', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($this->sessionLocale($request) ?? $this->headerLocale($request));

        return $next($request);
    }

    private function sessionLocale(Request $request): ?string
    {
        $locale = $request->session()->get('locale');

        return is_string($locale) && in_array($locale, self::SUPPORTED_LOCALES, true)
            ? $locale
            : null;
    }

    private function headerLocale(Request $request): string
    {
        foreach ($request->getLanguages() as $language) {
            $language = strtolower(strtok($language, '_-'));

            if ($language === 'en') {
                return 'en';
            }

            if ($language === 'pt') {
                return 'pt_BR';
            }
        }

        return 'pt_BR';
    }
}
