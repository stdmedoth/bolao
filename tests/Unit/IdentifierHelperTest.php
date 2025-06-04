<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class IdentifierHelperTest extends TestCase
{
    public function test_generate_identifier_creates_unique_valid_strings(): void
    {
        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $ids[] = generate_identifier();
        }

        foreach ($ids as $id) {
            $this->assertSame(5, strlen($id));
            $this->assertMatchesRegularExpression('/^[0-9]{4}[A-Z]$/', $id);
        }

        $this->assertCount(count(array_unique($ids)), $ids, 'Identifiers should be unique across calls');
    }
}

