<?php

declare(strict_types=1);

namespace axy\htpasswd\io;

use axy\htpasswd\errors\FileNotSpecified;

/**
 * The interface of i/o htpasswd "file"
 */
interface IFile
{
    public function load(): string;

    /**
     * @throws FileNotSpecified
     */
    public function save(string $content): void;

    public function setFileName(string $filename): void;
}
