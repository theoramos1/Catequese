<?php
use PHPUnit\Framework\TestCase;
use catechesis\DataValidationUtils;
use core\domain\Locale;

require_once __DIR__ . '/../core/DataValidationUtils.php';
require_once __DIR__ . '/../core/domain/Locale.php';

class DataValidationUtilsTest extends TestCase
{
    public function testValidatePhoneNumberValidMobile(): void
    {
        $this->assertTrue(DataValidationUtils::validatePhoneNumber('(11) 91234-5678', Locale::BRASIL));
    }

    public function testValidatePhoneNumberValidLandline(): void
    {
        $this->assertTrue(DataValidationUtils::validatePhoneNumber('(21) 1234-5678', Locale::BRASIL));
    }

    public function testValidatePhoneNumberInvalidMissingHyphen(): void
    {
        $this->assertFalse(DataValidationUtils::validatePhoneNumber('(21) 12345678', Locale::BRASIL));
    }

    public function testValidatePhoneNumberInvalidLength(): void
    {
        $this->assertFalse(DataValidationUtils::validatePhoneNumber('(211) 1234-5678', Locale::BRASIL));
    }

    public function testValidateZipCodeValid(): void
    {
        $this->assertTrue(DataValidationUtils::validateZipCode('12345-678', Locale::BRASIL));
    }

    public function testValidateZipCodeInvalidMissingHyphen(): void
    {
        $this->assertFalse(DataValidationUtils::validateZipCode('12345678', Locale::BRASIL));
    }

    public function testValidateZipCodeInvalidLength(): void
    {
        $this->assertFalse(DataValidationUtils::validateZipCode('1234-567', Locale::BRASIL));
    }
}
?>
