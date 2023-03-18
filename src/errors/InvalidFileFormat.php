<?php

declare(strict_types=1);

namespace axy\htpasswd\errors;

use axy\errors\Runtime;

/**
 * The htpasswd file has invalid format
 */
final class InvalidFileFormat extends Runtime implements Error
{
    /**
     * @var string
     */
    protected $defaultMessage = 'Htpasswd file {{ filename }} has invalid format';

    public function __construct(string $filename = null)
    {
        $this->filename = $filename;
        $message = [
            'filename' => $filename,
        ];
        parent::__construct($message);
    }

    public function getFileName(): string
    {
        return $this->filename;
    }

    private string $filename;
}
