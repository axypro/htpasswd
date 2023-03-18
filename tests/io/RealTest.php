<?php

declare(strict_types=1);

namespace axy\htpasswd\tests\io;

use axy\htpasswd\errors\FileNotSpecified;
use axy\htpasswd\io\Real;
use axy\htpasswd\tests\BaseTestCase;

/**
 * coversDefaultClass axy\htpasswd\io\Real
 */
class RealTest extends BaseTestCase
{
    /**
     * covers ::load
     * covers ::save
     */
    public function testLoadSave(): void
    {
        $fn = $this->tmpDir()->getPath('real.txt', make: true, clear: true);
        $content = 'This is content of the file' . PHP_EOL;
        $real = new Real($fn);
        $this->assertSame('', $real->load());
        $this->assertFileDoesNotExist($fn);
        $real->save($content);
        $this->assertFileExists($fn);
        $this->assertSame($content, file_get_contents($fn));
        $this->assertSame($content, $real->load());
        $real2 = new Real($fn);
        $this->assertSame($content, $real2->load());
    }

    /**
     * covers ::load
     * covers ::save
     */
    public function testNotSpecified(): void
    {
        $real = new Real(null);
        $this->assertSame('', $real->load());
        $this->expectException(FileNotSpecified::class);
        $real->save('Content');
    }

    /**
     * covers ::setFileName
     */
    public function testSetFilename(): void
    {
        $real = new Real(null);
        $fn = __DIR__ . '/../tst/invalid';
        $real->setFileName($fn);
        $this->assertSame(file_get_contents($fn), $real->load());
    }
}
