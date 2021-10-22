<?php

namespace Draw\Component\Profiling;

interface ProfilerInterface
{
    public function start();

    public function stop();

    public function getType();
}
