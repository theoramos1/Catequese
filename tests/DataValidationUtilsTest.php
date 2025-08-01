<?php
use PHPUnit\Framework\TestCase;
use catechesis\DataValidationUtils;
use core\domain\Locale;

require_once __DIR__ . '/../core/DataValidationUtils.php';
require_once __DIR__ . '/../core/domain/Locale.php';

class DataValidationUtilsTest extends TestCase
{
    public function testValidatePhoneNumberValidMobileFormatted(): void
    {
        $this->assertTrue(DataValidationUtils::validatePhoneNumber('(11) 91234-5678', Locale::BRASIL));
    }

    public function testValidatePhoneNumberValidMobileDigits(): void
    {
        $this->assertTrue(DataValidationUtils::validatePhoneNumber('11912345678', Locale::BRASIL));
    }

    public function testValidatePhoneNumberValidLandlineFormatted(): void
    {
        $this->assertTrue(DataValidationUtils::validatePhoneNumber('(21) 1234-5678', Locale::BRASIL));
    }

    public function testValidatePhoneNumberValidLandlineDigits(): void
    {
        $this->assertTrue(DataValidationUtils::validatePhoneNumber('2112345678', Locale::BRASIL));
    }

    public function testValidatePhoneNumberValidMissingHyphen(): void
    {
        $this->assertTrue(DataValidationUtils::validatePhoneNumber('(21) 12345678', Locale::BRASIL));
    }

    public function testValidatePhoneNumberInvalidLength(): void
    {
        $this->assertFalse(DataValidationUtils::validatePhoneNumber('(211) 1234-5678', Locale::BRASIL));
    }

    public function testValidatePhoneNumberInvalidMobileWithoutNine(): void
    {
        $this->assertFalse(DataValidationUtils::validatePhoneNumber('(11) 81234-5678', Locale::BRASIL));
    }

    public function testValidatePhoneNumberInvalidLandlineWithNine(): void
    {
        $this->assertFalse(DataValidationUtils::validatePhoneNumber('2191234567', Locale::BRASIL));
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
