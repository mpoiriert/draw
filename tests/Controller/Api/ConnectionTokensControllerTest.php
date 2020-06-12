<?php

namespace App\Tests\Controller\Api;

use App\Tests\TestCase;
use Draw\Bundle\DashboardBundle\Feedback\DefaultHeader;
use Draw\Bundle\DashboardBundle\Feedback\SignedOut;
use Draw\Bundle\DashboardBundle\Tester\ResponseContainFeedbackTester;
use Firebase\JWT\JWT;

class ConnectionTokensControllerTest extends TestCase
{
    public function testOptionsCreate()
    {
        $this->httpTester()
            ->options('/api/connection-tokens')
            ->assertStatus(200);
    }

    public function testRefresh()
    {
        $token = JWT::encode(
            [
                'id' => 'invalid',
                'exp' => (new \DateTime('+ 7 days'))->getTimestamp(),
            ],
            'acme',
            'HS256'
        );

        $this->httpTester()
            ->post(
                '/api/connection-tokens/refresh',
                '',
                ['Authorization' => 'Bearer '.$token]
            )
            ->assertStatus(403)
            ->test(new ResponseContainFeedbackTester(SignedOut::FEEDBACK_TYPE))
            ->test(
                new ResponseContainFeedbackTester(
                    DefaultHeader::FEEDBACK_TYPE,
                    ['name' => 'Authorization', 'clear' => true, 'value' => null]
                )
            );
    }
}
