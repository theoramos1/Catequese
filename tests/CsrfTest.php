<?php
use PHPUnit\Framework\TestCase;
use catechesis\Utils;

require_once __DIR__ . '/../core/Utils.php';

class CsrfTest extends TestCase
{
    public function testInvalidTokenIsRejected(): void
    {
        $_SESSION = [];
        $token = Utils::getCSRFToken();
        $this->assertFalse(Utils::verifyCSRFToken('invalid')); // mismatch
    }
}
?>
