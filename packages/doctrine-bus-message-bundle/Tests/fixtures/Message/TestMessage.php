<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Tests\fixtures\Message;

use Draw\Bundle\DoctrineBusMessageBundle\Message\AsyncMessageInterface;

class TestMessage implements AsyncMessageInterface
{
    public $value = null;
}
