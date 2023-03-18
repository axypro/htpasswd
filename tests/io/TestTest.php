<?php

declare(strict_types=1);

namespace axy\htpasswd\tests\io;

use axy\htpasswd\io\Test;
use axy\htpasswd\tests\BaseTestCase;

/**
 * coversDefaultClass axy\htpasswd\io\Test
 */
class TestTest extends BaseTestCase
{
    /**
     * covers ::load
     * covers ::save
     */
    public function testLoadSave()
    {
        $test = new Test();
        $this->assertSame('', $test->load());
        $test->save("One\nTwo\nThree");
        $this->assertSame("One\nTwo\nThree", $test->load());
        $this->assertSame(['One', 'Two', 'Three'], $test->getLines());
        $test->save('X');
        $this->assertSame('X', $test->load());
        $this->assertSame(['X'], $test->getLines());
    }

    /**
     * covers ::load
     */
    public function testConstructString()
    {
        $test = new Test("One\nTwo");
        $this->assertSame("One\nTwo", $test->load());
        $this->assertSame(['One', 'Two'], $test->getLines());
    }


    /**
     * covers ::load
     */
    public function testConstructArray()
    {
        $test = new Test(['One', 'Two']);
        $this->assertSame("One\nTwo", $test->load());
        $this->assertSame(['One', 'Two'], $test->getLines());
    }

    /**
     * covers ::setFileName
     */
    public function testSetFilename()
    {
        $test = new Test('test');
        $test->setFileName('some/file');
        $this->assertSame('test', $test->load());
    }
}
