<?php
namespace catechesis {
    function curl_init($url = null) {
        return \curl_init($url);
    }

    function curl_exec($ch) {
        return \PaymentVerificationServiceTest::$mockResponse['exec'];
    }

    function curl_getinfo($ch, $option) {
        if ($option === CURLINFO_HTTP_CODE) {
            return \PaymentVerificationServiceTest::$mockResponse['status'];
        }
        return null;
    }

    function curl_close($ch) {
        \curl_close($ch);
    }
}

namespace {
    use catechesis\PaymentVerificationService;
    use PHPUnit\Framework\TestCase;

    require_once __DIR__ . '/../core/PaymentVerificationService.php';

    class PaymentVerificationServiceTest extends TestCase
    {
        public static array $mockResponse;

    public function testVerifyPaymentSuccess(): void
    {
        self::$mockResponse = ['exec' => json_encode(['paid' => true, 'amount' => 20]), 'status' => 200];
        $service = new PaymentVerificationService('http://example', 'token', 10);
        $this->assertTrue($service->verifyPayment(1, '123', 10));
    }

    public function testVerifyPaymentInsufficientAmount(): void
    {
        self::$mockResponse = ['exec' => json_encode(['paid' => true, 'amount' => 5]), 'status' => 200];
        $service = new PaymentVerificationService('http://example', 'token', 10);
        $this->assertFalse($service->verifyPayment(1, '123', 10));
    }

    public function testVerifyPaymentFailure(): void
    {
        self::$mockResponse = ['exec' => false, 'status' => 0];
        $service = new PaymentVerificationService('http://example', 'token', 10);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to connect to Pix provider.');
        $service->verifyPayment(1, '123', 10);
    }

    public function testVerifyPaymentInvalidResponse(): void
    {
        self::$mockResponse = ['exec' => 'invalid', 'status' => 200];
        $service = new PaymentVerificationService('http://example', 'token', 10);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid response from Pix provider.');
        $service->verifyPayment(1, '123', 10);
    }
    }
}

