<?php

declare(strict_types=1);

namespace axy\htpasswd\io;

/**
 * The interface of i/o htpasswd "file"
 */
interface IFile
{
    /**
     * @return string
     */
    public function load();

    /**
     * @param string $content
     * @throws \axy\htpasswd\errors\FileNotSpecified
     */
    public function save($content);

    /**
     * @param string $filename
     */
    public function setFileName($filename);
}
