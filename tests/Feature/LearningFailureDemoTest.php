<?php

namespace Tests\Feature;

use Tests\TestCase;

class LearningFailureDemoTest extends TestCase
{
    public function test_intentional_failure_demo(): void
    {
        $shouldFail = filter_var(getenv('DEMO_FAILING_TEST') ?: false, FILTER_VALIDATE_BOOL);

        if ($shouldFail) {
            $this->fail('Intentional failure for CI learning demo.');
        }

        $this->addToAssertionCount(1);
    }
}
