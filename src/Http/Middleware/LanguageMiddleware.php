<?php

namespace Makiomar\WooOrderDashboard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Makiomar\WooOrderDashboard\Helpers\LanguageHelper;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Initialize language from request
        LanguageHelper::initializeLanguage();
        
        // Share language data with all views
        view()->share('currentLanguage', LanguageHelper::getCurrentLanguage());
        view()->share('isRTL', LanguageHelper::isRTL());
        view()->share('rtlClass', LanguageHelper::getRTLClass());
        view()->share('textDirection', LanguageHelper::getTextDirection());
        view()->share('languageSwitcherData', LanguageHelper::getLanguageSwitcherData());
        
        return $next($request);
    }
} 