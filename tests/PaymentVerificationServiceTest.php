<?php
namespace catechesis {
    // Curl functions are stubbed globally via CurlStubs.php
}

namespace {
    use catechesis\PaymentVerificationService;
    use PHPUnit\Framework\TestCase;

    require_once __DIR__ . '/../core/PaymentVerificationService.php';

    class PaymentVerificationServiceTest extends TestCase
    {

    public function testVerifyPaymentSuccess(): void
    {
        \catechesis\CurlStubs::$mockResponse = ['exec' => json_encode(['paid' => true, 'amount' => 20]), 'status' => 200];
        $service = new PaymentVerificationService('http://example', 'token', 10);
        $this->assertTrue($service->verifyPayment(1, '123', 10));
    }

    public function testVerifyPaymentInsufficientAmount(): void
    {
        \catechesis\CurlStubs::$mockResponse = ['exec' => json_encode(['paid' => true, 'amount' => 5]), 'status' => 200];
        $service = new PaymentVerificationService('http://example', 'token', 10);
        $this->assertFalse($service->verifyPayment(1, '123', 10));
    }

    public function testVerifyPaymentFailure(): void
    {
        \catechesis\CurlStubs::$mockResponse = ['exec' => false, 'status' => 0];
        $service = new PaymentVerificationService('http://example', 'token', 10);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to connect to Pix provider.');
        $service->verifyPayment(1, '123', 10);
    }

    public function testVerifyPaymentInvalidResponse(): void
    {
        \catechesis\CurlStubs::$mockResponse = ['exec' => 'invalid', 'status' => 200];
        $service = new PaymentVerificationService('http://example', 'token', 10);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid response from Pix provider.');
        $service->verifyPayment(1, '123', 10);
    }
    }
}

