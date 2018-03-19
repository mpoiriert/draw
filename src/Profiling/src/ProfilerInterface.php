<?php

namespace Draw\Profiling;

interface ProfilerInterface
{
    public function start();

    public function stop();

    public function getType();
}