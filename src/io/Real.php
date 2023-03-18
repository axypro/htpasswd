<?php

declare(strict_types=1);

namespace axy\htpasswd\io;

use axy\htpasswd\errors\FileNotSpecified;

/**
 * Real file
 */
class Real implements IFile
{
    public function __construct(private ?string $filename)
    {
    }

    public function load(): string
    {
        if (($this->filename === null) || (!is_file($this->filename))) {
            return '';
        }
        return file_get_contents($this->filename);
    }

    public function save(string $content): void
    {
        if ($this->filename === null) {
            throw new FileNotSpecified();
        }
        file_put_contents($this->filename, $content);
    }

    public function setFileName(string $filename): void
    {
        $this->filename = $filename;
    }
}
