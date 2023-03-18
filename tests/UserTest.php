<?php

declare(strict_types=1);

namespace axy\htpasswd\tests;

use axy\htpasswd\User;
use axy\htpasswd\PasswordFile;

/**
 * coversDefaultClass axy\htpasswd\User
 */
class UserTest extends BaseTestCase
{
    /**
     * covers ::getFileLine
     * covers ::getName
     */
    public function testGetFileLine(): void
    {
        $user = new User('nick', 'qq0e00d');
        $this->assertSame('nick:qq0e00d', $user->getFileLine());
        $this->assertSame('nick:qq0e00d', (string)$user);
        $this->assertSame('nick', $user->name);
    }

    /**
     * covers ::verify
     */
    public function testVerify(): void
    {
        $user = new User('nick', '$apr1$aGwevNmX$4WQ0UxE4TzhoaE6QkeBJJ0');
        $this->assertTrue($user->verify('password'));
        $this->assertFalse($user->verify('another'));
    }

    /**
     * covers ::setPassword
     */
    public function testSetPassword(): void
    {
        $user = new User('nick', '$apr1$aGwevNmX$4WQ0UxE4TzhoaE6QkeBJJ0');
        $user->setPassword('another', PasswordFile::ALG_SHA1);
        $this->assertFalse($user->verify('password'));
        $this->assertTrue($user->verify('another'));
        $this->assertSame('nick:{SHA}t8j/uPvGfBcTKODo9kNpTo5hszU=', $user->getFileLine());
    }

    /**
     * covers ::loadFromFileLine
     * @dataProvider providerLoadFromFileLine
     */
    public function testLoadFromFileLine(string $line, bool $success): void
    {
        if ($success) {
            $user = User::loadFromFileLine($line);
            $this->assertInstanceOf('axy\htpasswd\User', $user);
            $this->assertSame($line, $user->getFileLine());
        } else {
            $this->assertNull(User::loadFromFileLine($line));
        }
    }

    public static function providerLoadFromFileLine(): array
    {
        return [
            ['nick:$apr1$aGwevNmX$4WQ0UxE4TzhoaE6QkeBJJ0', true],
            ['nick:invalid:line', true],
            ['nick', false],
            ['', false],
        ];
    }
}
