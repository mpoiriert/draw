<?php

namespace Draw\Profiling;

interface MetricBuilderInterface
{
    public function build();

    public function getType();
}