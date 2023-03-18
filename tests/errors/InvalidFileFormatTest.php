<?php

declare(strict_types=1);

namespace axy\htpasswd\tests\errors;

use axy\htpasswd\errors\InvalidFileFormat;
use axy\htpasswd\tests\BaseTestCase;

/**
 * coversDefaultClass axy\htpasswd\errors\InvalidFileFormat
 */
class InvalidFileFormatTest extends BaseTestCase
{
    /**
     * covers ::getFileName
     */
    public function testCreate()
    {
        $e = new InvalidFileFormat('/tmp/htpasswd');
        $this->assertSame('/tmp/htpasswd', $e->getFileName());
        $this->assertSame('Htpasswd file /tmp/htpasswd has invalid format', $e->getMessage());
    }
}
