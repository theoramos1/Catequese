<?php
namespace catechesis {
    function file_get_contents($filename, $use_include_path = false, $context = null) {
        return \PaymentVerificationServiceTest::$mockResponse;
    }
}

use catechesis\PaymentVerificationService;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../core/PaymentVerificationService.php';

class PaymentVerificationServiceTest extends TestCase
{
    public static $mockResponse;

    public function testVerifyPaymentSuccess(): void
    {
        self::$mockResponse = json_encode(['paid' => true, 'amount' => 20]);
        $service = new PaymentVerificationService('http://example', 'token');
        $this->assertTrue($service->verifyPayment(1, '123', 10));
    }

    public function testVerifyPaymentInsufficientAmount(): void
    {
        self::$mockResponse = json_encode(['paid' => true, 'amount' => 5]);
        $service = new PaymentVerificationService('http://example', 'token');
        $this->assertFalse($service->verifyPayment(1, '123', 10));
    }

    public function testVerifyPaymentFailure(): void
    {
        self::$mockResponse = false;
        $service = new PaymentVerificationService('http://example', 'token');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to connect to payment provider.');
        $service->verifyPayment(1, '123', 10);
    }

    public function testVerifyPaymentInvalidResponse(): void
    {
        self::$mockResponse = 'invalid';
        $service = new PaymentVerificationService('http://example', 'token');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid response from payment provider.');
        $service->verifyPayment(1, '123', 10);
    }
}
?>
