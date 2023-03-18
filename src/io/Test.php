<?php

declare(strict_types=1);

namespace axy\htpasswd\io;

/**
 * Htpasswd file mock for tests
 */
class Test implements IFile
{
    /**
     * The constructor
     *
     * @param string|string[] $content [optional]
     */
    public function __construct(string|array|null $content = null)
    {
        if ($content === null) {
            $content = '';
        } elseif (is_array($content)) {
            $content = implode(PHP_EOL, $content);
        }
        $this->content = $content;
    }

    public function load(): string
    {
        return $this->content;
    }

    public function save(string $content): void
    {
        $this->content = $content;
    }

    public function setFileName(string $content): void
    {
    }

    /** Returns the content as an array of lines */
    public function getLines(): array
    {
        return explode("\n", str_replace("\r", '', $this->content));
    }

    private string $content;
}
