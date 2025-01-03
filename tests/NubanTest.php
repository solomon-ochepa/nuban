<?php

use SolomonOchepa\Nuban\Nuban;
use Tests\TestCase;

class NubanTest extends TestCase
{
    public function test_get_account_details()
    {
        $nuban = app(Nuban::class);
        $details = $nuban->getAccountDetails('9036604001', '999992'); // put valid account number

        $this->assertArrayHasKey('account_number', $details);
        $this->assertArrayHasKey('bank_code', $details);
        $this->assertArrayHasKey('Bank_name', $details);
        // add more assertions as needed
    }
}
