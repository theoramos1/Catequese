<?php

namespace catechesis;

require_once(__DIR__ . '/Configurator.php');
require_once(__DIR__ . '/domain/Locale.php');

use core\domain\Locale;

class Translation
{
    private static $translations = null;
    private static $fallback = null;
    private static $currentLocale = null;

    public static function t(string $key): string
    {
        $locale = Configurator::getConfigurationValueOrDefault(Configurator::KEY_LOCALIZATION_CODE);
        if (self::$translations === null || self::$currentLocale !== $locale) {
            self::loadLocale($locale);
        }
        if (isset(self::$translations[$key])) {
            return self::$translations[$key];
        }
        return self::$fallback[$key] ?? $key;
    }

    public static function setLocale(string $locale): void
    {
        self::loadLocale($locale);
    }

    private static function loadLocale(string $locale): void
    {
        self::$fallback = require(__DIR__ . '/../locale/pt_PT.php');
        if ($locale === Locale::BRASIL) {
            $br = require(__DIR__ . '/../locale/pt_BR.php');
            self::$translations = array_merge(self::$fallback, $br);
        } else {
            self::$translations = self::$fallback;
        }
        self::$currentLocale = $locale;
    }
}
