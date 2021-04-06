<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Git;

final class GitDiffProvider
{
    /**
     * @return string[] The absolute path to the file matching the git diff shell command.
     */
    public function provide(): array
    {
        $statusOutput = shell_exec('git status --porcelain') ?: '';
        $gitFiles = explode(PHP_EOL, trim($statusOutput));
        $files = [];
        foreach ($gitFiles as $gitFile) {
            $gitFile = preg_replace('/\s{2,}/', ' ', $gitFile);
            [$status, $path] = explode(' ', $gitFile);
            if (!in_array($status, ['??', 'D'])) {
                $files[] = $path;
            }
        }

        return array_values(array_filter(array_map('realpath', $files)));
    }
}
