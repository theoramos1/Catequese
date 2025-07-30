<?php
use PHPUnit\Framework\TestCase;
use catechesis\Pix;

require_once __DIR__ . '/../core/Pix.php';

class PixTest extends TestCase
{
    public function testPayloadWithoutAmountOmitsTag54(): void
    {
        $payload = Pix::buildPayload('key','Merchant','City','123', null, '');
        $this->assertStringNotContainsString('54', substr($payload, 0, strpos($payload, '6304')));
    }

    public function testPayloadWithAmountIncludesTag54(): void
    {
        $payload = Pix::buildPayload('key','Merchant','City','123', 10.50, '');
        $this->assertStringContainsString('54', $payload);
    }
}
?>
