<?php

namespace Draw\HttpTester;

interface ClientFactoryInterface
{
    /**
     * @return ClientInterface
     */
    public function createClient();
}