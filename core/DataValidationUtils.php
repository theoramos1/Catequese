<?php


namespace catechesis;

use core\domain\Locale;
use DateTime;


class DataValidationUtils
{

    /**
     * Checks if a date has the format 'dd/mm/YYYY' and if it is actually valid (e.g. a real calendar date).
     * @param string $date
     * @return bool
     */
    public static function validateDate(string $date)
    {
        $format = 'd/m/Y';
        $pattern = '/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/';
        $matches = array();
        $res = preg_match($pattern, $date, $matches);

        //Ensure that months and days always have 2 digits (reject otherwise)
        if($res)
        {
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) === $date;
        }
        else
            return false;
    }

    /**
     * Checks if a given string represents a valid Portuguese phone number.
     * If the optional parameter $checkAntiPatterns is true, also checks if the provided number matches a set
     * of known dummy phone numbers and returns false (invalid) if so.
     * @param string $tel
     * @param bool $checkAntiPatterns
     * @return bool
     */
    public static function validatePhoneNumber(string $tel, string $locale, bool $checkAntiPatterns = false)
    {
        if($locale==Locale::PORTUGAL)
        {
            $pattern = '/^(\+\d{1,}[-\s]{0,1})?\d{9}$/';
            $antipattern1 = "000000000";
            $antipattern2 = "111111111";
            $antipattern3 = "123456789";
            $match = preg_match($pattern, $tel);
        }
        else if($locale==Locale::BRASIL)
        {
            $mobilePattern = '/^\(\d{2}\) 9 \d{4}-\d{4}$/';
            $landlinePattern = '/^\(\d{2}\) \d{4}-\d{4}$/';
            $antipattern1 = "0000-0000";
            $antipattern2 = "1111-1111";
            $antipattern3 = "1234-5678";
            $match = preg_match($mobilePattern, $tel) || preg_match($landlinePattern, $tel);
        }
        else
        {
            $match = false;
        }

        return $match && (!$checkAntiPatterns ||
                (strpos($tel, $antipattern1)===false && strpos($tel, $antipattern2)===false && strpos($tel, $antipattern3)===false));
    }

    /**
     * Checks if a given string looks like a valid Portuguese zip code.
     * @param $postal
     * @return false|int
     */
    public static function validateZipCode(string $postal, string $locale)
    {
        $pattern = '';
        if($locale == Locale::PORTUGAL)
            $pattern = '/^[0-9]{4}\-[0-9]{3}\s\S+/';
        else if($locale == Locale::BRASIL)
            // Brazilian zip code without locality
            $pattern = '/^[0-9]{5}-[0-9]{3}$/';

        return (preg_match($pattern, $postal));
    }

    /**
     * Checks if a string is a valid e-mail address.
     * @param $email
     * @return false|int
     */
    public static function validateEmail($email)
    {
        $pattern = '/.+@.+\..+\S+/';
        return (preg_match($pattern, $email));
    }


    /**
     * Checks if a string is a valid URL.
     * @param $url
     * @return mixed
     */
    public static function validateURL($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }


    /**
     * Returns true if the provided string is a valid URL and belongs to this CatecheSis instance.
     * If, otherwise, the URL points to another site, outside of this CatecheSis instance, returns false.
     * @param string $url
     * @return bool
     */
    public static function checkInnerURL(string $url)
    {
        if(!self::validateURL($url))
            return false;
        $parts = parse_url($url);
        $catecheSisProtocol = (constant('CATECHESIS_HTTPS')?"https":"http");
        return $parts['scheme']==$catecheSisProtocol && $parts['host']==constant('CATECHESIS_DOMAIN');
    }


    /**
     * Checks whether a given username has only characters considered valid by ulogin.
     * @param string $password
     * @return bool
     */
    public static function validateUsername(string $username)
    {
        $pattern = '~^[\p{L}\p{M}\p{Nd}\._@/+-]*[\p{L}\p{M}\p{Nd}]+[\p{L}\p{M}\p{Nd}\._@/+-]*$~u';
        $MAX_LENGTH = 100;
        return (strlen($username)<=$MAX_LENGTH && preg_match($pattern, $username));
    }

    /**
     * Checks whether a given password has at least length 10, and contains letters and numbers.
     * @param string $password
     * @return bool
     */
    public static function validatePassword(string $password)
    {
        $MIN_LENGHT = 10;
        $letterNumber = '/^(?=.*[a-zA-Z])(?=.*[0-9])/';
        return (strlen($password)>=$MIN_LENGHT && preg_match($letterNumber, $password));
    }

    /**
     * Validates a Multibanco entity number. It should contain exactly five digits.
     * @param string $value
     * @return bool
     */
    public static function validateMbEntity(string $value)
    {
        return preg_match('/^[0-9]{5}$/', $value);
    }

    /**
     * Validates a Multibanco reference number. It should contain exactly nine digits.
     * @param string $value
     * @return bool
     */
    public static function validateMbReference(string $value)
    {
        return preg_match('/^[0-9]{9}$/', $value);
    }

    /**
     * Checks whether a numeric string represents a positive float value.
     * @param string $value
     * @return bool
     */
    public static function validatePositiveFloat(string $value)
    {
        return is_numeric($value) && floatval($value) > 0;
    }
}