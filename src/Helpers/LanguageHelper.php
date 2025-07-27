<?php

namespace Makiomar\WooOrderDashboard\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageHelper
{
    /**
     * Get current language
     */
    public static function getCurrentLanguage()
    {
        return Session::get('woo_language', config('woo-order-dashboard.language.default', 'ar'));
    }

    /**
     * Set current language
     */
    public static function setLanguage($language)
    {
        $availableLanguages = config('woo-order-dashboard.language.available', ['ar', 'en']);
        
        \Log::info('LanguageHelper::setLanguage', [
            'requested_language' => $language,
            'available_languages' => $availableLanguages,
            'is_valid' => in_array($language, $availableLanguages),
        ]);
        
        if (in_array($language, $availableLanguages)) {
            Session::put('woo_language', $language);
            App::setLocale($language);
            \Log::info('Language set successfully', ['language' => $language]);
            return true;
        }
        
        \Log::warning('Invalid language requested', ['language' => $language]);
        return false;
    }

    /**
     * Get available languages
     */
    public static function getAvailableLanguages()
    {
        return config('woo-order-dashboard.language.available', ['ar', 'en']);
    }

    /**
     * Get language names
     */
    public static function getLanguageNames()
    {
        return config('woo-order-dashboard.language.names', [
            'ar' => 'العربية',
            'en' => 'English',
        ]);
    }

    /**
     * Check if current language is RTL
     */
    public static function isRTL()
    {
        $rtlLanguages = config('woo-order-dashboard.language.rtl', ['ar']);
        return in_array(self::getCurrentLanguage(), $rtlLanguages);
    }

    /**
     * Get RTL class for current language
     */
    public static function getRTLClass()
    {
        return self::isRTL() ? 'rtl' : '';
    }

    /**
     * Get text direction for current language
     */
    public static function getTextDirection()
    {
        return self::isRTL() ? 'rtl' : 'ltr';
    }

    /**
     * Translate text using the package namespace
     */
    public static function trans($key, $replacements = [], $locale = null)
    {
        $locale = $locale ?: self::getCurrentLanguage();
        return __("woo-order-dashboard::$key", $replacements, $locale);
    }

    /**
     * Get language switcher data
     */
    public static function getLanguageSwitcherData()
    {
        $currentLanguage = self::getCurrentLanguage();
        $availableLanguages = self::getAvailableLanguages();
        $languageNames = self::getLanguageNames();
        
        $languages = [];
        foreach ($availableLanguages as $code) {
            $languages[] = [
                'code' => $code,
                'name' => $languageNames[$code] ?? $code,
                'is_current' => $code === $currentLanguage,
                'url' => self::getLanguageSwitchUrl($code),
            ];
        }
        
        return $languages;
    }

    /**
     * Get language switch URL
     */
    public static function getLanguageSwitchUrl($language)
    {
        return request()->fullUrlWithQuery(['lang' => $language]);
    }

    /**
     * Initialize language from request
     */
    public static function initializeLanguage()
    {
        $requestedLanguage = request()->get('lang');
        
        \Log::info('LanguageHelper::initializeLanguage', [
            'requested_language' => $requestedLanguage,
            'session_language' => session('woo_language'),
            'default_language' => config('woo-order-dashboard.language.default', 'ar'),
        ]);
        
        if ($requestedLanguage) {
            $result = self::setLanguage($requestedLanguage);
            \Log::info('Language set result', ['result' => $result]);
        } else {
            $currentLanguage = self::getCurrentLanguage();
            App::setLocale($currentLanguage);
            \Log::info('Using current language', ['language' => $currentLanguage]);
        }
    }
} 