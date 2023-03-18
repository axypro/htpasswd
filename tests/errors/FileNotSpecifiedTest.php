<?php

declare(strict_types=1);

namespace axy\htpasswd\tests\errors;

use axy\htpasswd\errors\FileNotSpecified;
use axy\htpasswd\tests\BaseTestCase;

/**
 * coversDefaultClass axy\htpasswd\errors\FileNotSpecified
 */
class FileNotSpecifiedTest extends BaseTestCase
{
    /**
     * covers ::getFileName
     */
    public function testCreate()
    {
        $e = new FileNotSpecified();
        $this->assertSame('Htpasswd file is not specified', $e->getMessage());
    }
}
