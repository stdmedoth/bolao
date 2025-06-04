<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class IdentifierHelperTest extends TestCase
{
    public function test_generate_identifier_format()
    {
        require_once __DIR__.'/../../app/Helpers/IdentifierHelper.php';
        $identifier = generate_identifier();
        $this->assertMatchesRegularExpression('/^\d{4}[A-Z]$/', $identifier);
    }
}
