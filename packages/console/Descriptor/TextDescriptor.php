<?php

namespace Draw\Component\Console\Descriptor;

use Symfony\Component\Console\Command\Command;

class TextDescriptor extends \Symfony\Component\Console\Descriptor\TextDescriptor
{
    protected function describeCommand(Command $command, array $options = []): void
    {
        $this->writeText('<comment>Command Name:</comment>', $options);
        $this->writeText("\n");
        $this->writeText('  '.$command->getName());
        $this->writeText("\n\n");

        parent::describeCommand($command, $options);
    }

    private function writeText(string $content, array $options = []): void
    {
        $this->write(
            isset($options['raw_text']) && $options['raw_text'] ? strip_tags($content) : $content,
            isset($options['raw_output']) ? !$options['raw_output'] : true
        );
    }
}
