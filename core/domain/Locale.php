<?php

namespace core\domain;

use Exception;

abstract class Locale
{
    const PORTUGAL = "PT";
    const BRASIL = "BR";

    /**
     * Returns the appropriate language tag to be used in the HTML
     * lang attribute for the provided locale.
     *
     * @param string $locale Country code as stored in configuration
     * @return string Language tag (e.g. "pt" or "pt-br")
     */
    public static function htmlLang(string $locale)
    {
        switch($locale)
        {
            case self::BRASIL:
                return "pt-br";

            case self::PORTUGAL:
            default:
                return "pt";
        }
    }


    public static function catechesisStartMonth(string $locale)
    {
        switch($locale)
        {
            case self::PORTUGAL:
            default:
                return "September";

            case self::BRASIL:
                return "March";
        }
    }

    public static function catechesisEndMonth(string $locale)
    {
        switch($locale)
        {
            case self::PORTUGAL:
            default:
                return "June";

            case self::BRASIL:
                return "November";
        }
    }
}