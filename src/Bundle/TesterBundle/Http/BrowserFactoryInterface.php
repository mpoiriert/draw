<?php

namespace Draw\Bundle\TesterBundle\Http;

use Symfony\Component\BrowserKit\AbstractBrowser;

interface BrowserFactoryInterface
{
    public function createBrowser(): AbstractBrowser;
}
