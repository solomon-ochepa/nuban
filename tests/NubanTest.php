<?php

use SolomonOchepa\Nuban\Nuban;
use Tests\TestCase;

class NubanTest extends TestCase
{
    public function test_get_account_details()
    {
        $nuban = app(Nuban::class);
        $details = $nuban->account('1234567890', '0123'); // put valid account number

        $this->assertArrayHasKey('account_number', $details);
        $this->assertArrayHasKey('bank_code', $details);
        $this->assertArrayHasKey('Bank_name', $details);
        // add more assertions as needed
    }
}
