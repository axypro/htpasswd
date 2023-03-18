<?php

declare(strict_types=1);

namespace axy\htpasswd\tests;

use axy\htpasswd\errors\FileNotSpecified;
use axy\htpasswd\errors\InvalidFileFormat;
use axy\htpasswd\PasswordFile;
use axy\htpasswd\io\Test;
use axy\crypt\BCrypt;

/**
 * coversDefaultClass axy\htpasswd\PasswordFile
 */
class PasswordFileTest extends BaseTestCase
{
    /**
     * covers ::getFileName
     */
    public function testGetFileName(): void
    {
        $file = new PasswordFile('/tmp/htpasswd');
        $this->assertSame('/tmp/htpasswd', $file->getFileName());
    }

    /**
     * covers ::setPassword
     * covers ::save
     * covers ::isUserExists
     */
    public function testSetPassword(): void
    {
        $io = new Test();
        $file = new PasswordFile($io);
        $this->assertTrue($file->setPassword('one', 'one-password', PasswordFile::ALG_PLAIN));
        $this->assertTrue($file->isUserExist('one'));
        $this->assertFalse($file->isUserExist('two'));
        $this->assertTrue($file->setPassword('two', 'two-password', PasswordFile::ALG_PLAIN));
        $this->assertTrue($file->isUserExist('two'));
        $this->assertSame([''], $io->getLines());
        $file->save();
        $file2 = new PasswordFile($io);
        $this->assertTrue($file->isUserExist('one'));
        $this->assertFalse($file2->setPassword('one', 'new-password', PasswordFile::ALG_PLAIN));
        $this->assertTrue($file2->setPassword('three', 'three-password', PasswordFile::ALG_PLAIN));
        $file2->save();
        $expected = [
            'one:new-password',
            'two:two-password',
            'three:three-password',
            '',
        ];
        $this->assertSame($expected, $io->getLines());
    }

    /**
     * covers ::verify
     */
    public function testRealVerify(): void
    {
        $file = new PasswordFile(__DIR__ . '/tst/test');
        $this->assertTrue($file->isUserExist('one'));
        $this->assertTrue($file->isUserExist('two'));
        $this->assertFalse($file->isUserExist('three'));
        $this->assertTrue($file->verify('one', 'one-password'));
        $this->assertTrue($file->verify('two', 'two-password'));
        $this->assertFalse($file->verify('three', 'three-password'));
        $this->assertFalse($file->verify('one', 'two-password'));
        $this->assertFalse($file->verify('two', 'none'));
    }

    /**
     * covers ::save
     */
    public function testRealSave(): void
    {
        $fn = $this->tmpDir()->getPath('test', make: true, clear: true);
        $file = new PasswordFile($fn);
        $file->setPassword('one', 'three');
        $file->setPassword('two', 'four');
        $this->assertFileDoesNotExist($fn);
        $file->save();
        $this->assertFileExists($fn);
        $file2 = new PasswordFile($fn);
        $this->assertTrue($file2->isUserExist('one'));
        $this->assertTrue($file2->isUserExist('two'));
        $this->assertFalse($file2->isUserExist('three'));
        $this->assertFalse($file2->isUserExist('five'));
        $this->assertTrue($file2->verify('one', 'three'));
        $this->assertTrue($file2->verify('two', 'four'));
    }

    /**
     * covers ::load
     */
    public function testInvalid(): void
    {
        $file = new PasswordFile(__DIR__ . '/tst/invalid');
        $this->expectException(InvalidFileFormat::class);
        $file->isUserExist('one');
    }

    /**
     * covers ::load
     */
    public function testInvalidMock(): void
    {
        $mock = new Test('invalid');
        $file = new PasswordFile($mock);
        $this->expectException(InvalidFileFormat::class);
        $file->isUserExist('one');
    }

    /**
     * covers ::remove
     * covers ::setFileName
     */
    public function testRemove(): void
    {
        $fnSource = __DIR__ . '/tst/test';
        $fn = $this->tmpDir()->getPath('test', make: true, clear: true);
        $file = new PasswordFile($fnSource);
        $file->setFileName($fn);
        $this->assertFalse($file->remove('none'));
        $this->assertTrue($file->remove('one'));
        $this->assertFalse($file->remove('one'));
        $this->assertFalse($file->isUserExist('one'));
        $this->assertTrue($file->isUserExist('two'));
        $file->save();
        $this->assertFileExists($fn);
        $expected = 'two:$apr1$Hcy4Z1A2$OhLViOzdKWWIuF..c/90U0';
        $this->assertSame($expected, trim(file_get_contents($fn)));
    }

    public function testFileNotSpecified(): void
    {
        $file = new PasswordFile();
        $this->assertNull($file->getFileName());
        $file->setPassword('nick', 'pass', PasswordFile::ALG_PLAIN);
        $this->assertSame('nick:pass' . PHP_EOL, $file->getContent());
        $this->assertTrue($file->isUserExist('nick'));
        $this->assertFalse($file->isUserExist('pass'));
        $this->expectException(FileNotSpecified::class);
        $file->save();
    }

    public function testHashOptions(): void
    {
        $file = new PasswordFile();
        $file->setPassword('nick', 'pass', PasswordFile::ALG_BCRYPT, ['cost' => 6]);
        $content = trim($file->getContent());
        $pattern = '~^nick:(\$2y\$06\$[A-Za-z0-9/.]{53})$~is';
        $this->assertTrue((bool)preg_match($pattern, $content, $matches));
        $hash = $matches[1];
        $this->assertTrue(BCrypt::verify('pass', $hash));
    }

    /**
     * Bug #3
     */
    public function testEmptyContent(): void
    {
        $file = new PasswordFile(null);
        $file->getContent();
        $this->assertTrue(true, 'not thrown');
    }
}
