<?php

namespace Draw\Bundle\DashboardBundle\Tests\Controller;

use App\Tests\TestCase;

class OptionsControllerTest extends TestCase
{
    public function test()
    {
        $this->httpTester()
            ->options('/api/users')
            ->assertStatus(200);
    }
}
