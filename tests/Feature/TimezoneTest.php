<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Tests\TestCase;

class TimezoneTest extends TestCase
{
    public function test_application_uses_asia_jakarta_timezone(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('Asia/Jakarta', now()->timezoneName);
        $this->assertSame('Asia/Jakarta', Carbon::now()->timezoneName);
    }
}
