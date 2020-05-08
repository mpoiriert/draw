<?php namespace Draw\Bundle\DashboardBundle\Tester;

use Draw\Component\Tester\DataTester;
use Draw\Component\Tester\Http\TestResponse;
use PHPUnit\Framework\Assert;

class ResponseContainFeedbackTester
{
    private $type;

    private $metadata = null;

    public function __construct($type, $metadata = null)
    {
        $this->type = $type;
        $this->metadata = $metadata;
    }

    public function __invoke(DataTester $tester)
    {
        foreach ($this->buildFeedbackList($tester->getData()) as $feedback) {
            if ($feedback['type'] !== $this->type) {
                continue;
            }

            if ($this->metadata === null) {
                return;
            }

            if($this->metadata != $feedback['metadata']) {
                continue;
            }

            return;
        }

        Assert::fail(
            sprintf("No feedback of type [%s] is present. Expected metadata:\n%s",
                $this->type,
                json_encode($this->metadata, JSON_PRETTY_PRINT)
            )
        );
    }

    private function buildFeedbackList(TestResponse $testResponse)
    {
        $testResponse->assertHeader('X-Draw-Feedback');
        $feedbackList = [];
        foreach ($testResponse->getResponse()->getHeader('X-Draw-Feedback') as $header) {
            $feedbackList = array_merge($feedbackList, json_decode('[' . $header . ']', true));
        }

        return $feedbackList;
    }
}