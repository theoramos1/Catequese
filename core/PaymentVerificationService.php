<?php
namespace catechesis;

require_once __DIR__ . '/PaymentVerifierTrait.php';

/**
 * Service to verify Pix payments for an enrolment using an external API.
 */
class PaymentVerificationService
{
    use PaymentVerifierTrait;
}
