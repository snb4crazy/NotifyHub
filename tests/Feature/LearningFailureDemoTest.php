<?php

namespace Tests\Feature;

use Tests\TestCase;

class LearningFailureDemoTest extends TestCase
{
    public function test_intentional_failure_demo(): void
    {
        if (filter_var(env('DEMO_FAILING_TEST', false), FILTER_VALIDATE_BOOL)) {
            $this->fail('Intentional failure for CI learning demo.');
        }

        $this->assertTrue(true);
    }
}
