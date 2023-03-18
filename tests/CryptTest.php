<?php

declare(strict_types=1);

namespace axy\htpasswd\tests;

use axy\htpasswd\Crypt;
use axy\htpasswd\PasswordFile;
use axy\crypt\APR1;
use axy\crypt\BCrypt;

/**
 * coversDefaultClass axy\htpasswd\Crypt
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CryptTest extends BaseTestCase
{
    /**
     * covers ::hash
     */
    public function testHashMD5(): void
    {
        $hash = Crypt::hash('my-password', PasswordFile::ALG_MD5);
        $this->assertTrue(APR1::verify('my-password', $hash));
        $hash2 = Crypt::hash('default');
        $this->assertTrue(APR1::verify('default', $hash2));
    }

    /**
     * covers ::hash
     */
    public function testHashPlain(): void
    {
        $this->assertSame('my-password', Crypt::hash('my-password', PasswordFile::ALG_PLAIN));
    }

    /**
     * covers ::hash
     */
    public function testHashSha1(): void
    {
        $password = 'my-password';
        $sha1 = '{SHA}7b1eEZ+UutufmaZ6xv9MelIErWE=';
        $this->assertSame($sha1, Crypt::hash($password, PasswordFile::ALG_SHA1));
    }

    /**
     * covers ::hash
     */
    public function testHashCrypt(): void
    {
        $password = '123456';
        $hash = Crypt::hash($password, PasswordFile::ALG_CRYPT);
        $this->assertIsString($hash);
        $pattern = '~^([a-zA-Z0-9/.]{2})[a-zA-Z0-9/.]{11}$~s';
        if (!preg_match($pattern, $hash, $matches)) {
            $this->fail('Crypt pattern');
        }
        $this->assertSame($hash, crypt($password, $matches[1]));
    }

    /**
     * covers ::hash
     */
    public function testHashBCrypt(): void
    {
        $password = 'mypassword=123456';
        $hash = Crypt::hash($password, PasswordFile::ALG_BCRYPT);
        $this->assertIsString($hash);
        $this->assertMatchesRegularExpression('~^\$2y\$05\$[A-Za-z0-9/.]{53}$~is', $hash);
        $this->assertTrue(BCrypt::verify($password, $hash));
    }

    /**
     * covers ::hash
     */
    public function testHashBCryptCost(): void
    {
        $password = 'mypassword=123456';
        $hash = Crypt::hash($password, PasswordFile::ALG_BCRYPT, ['cost' => 7]);
        $this->assertIsString($hash);
        $this->assertMatchesRegularExpression('~^\$2y\$07\$[A-Za-z0-9/.]{53}$~is', $hash);
        $this->assertTrue(BCrypt::verify($password, $hash));
    }

    /**
     * covers ::hash
     */
    public function testHashUndefined(): void
    {
        $this->assertNull(Crypt::hash('password', 'undefined'));
    }

    /**
     * covers ::verify
     * @dataProvider providerVerify
     */
    public function testVerify(string $password, string $hash, bool $expected = true): void
    {
        $this->assertSame($expected, Crypt::verify($password, $hash));
    }

    public static function providerVerify(): array
    {
        return [
            'plain' => ['password', 'password'],
            'md5' => ['password', '$apr1$aGwevNmX$4WQ0UxE4TzhoaE6QkeBJJ0'],
            'sha1' => ['password', '{SHA}W6ph5Mm5Pz8GgiULbPgzG37mj9g='],
            'crypt' => ['password', 'rOVL0k/supDAY'],
            'bcrypt' => ['pass1234', '$2y$05$skTPtV45nT7GyeUIMrmUMuI8iqFPcvROoDoTI2oUXTCVaebvdeZmq'],
            'bcrypt-c4' => ['pass1234', '$2y$04$ce5G.RR1gl4/UiYqMinJo.pBM71xPFS4Q9MOSrgW1ptch0h.q6ytC'],
            'plain_as_md5' => ['$apr1$aGwevNmX$4WQ0UxE4TzhoaE6QkeBJJ0', '$apr1$aGwevNmX$4WQ0UxE4TzhoaE6QkeBJJ0'],
            'plain_as_sha1' => ['{SHA}W6ph5Mm5Pz8GgiULbPgzG37mj9g=', '{SHA}W6ph5Mm5Pz8GgiULbPgzG37mj9g='],
            'plain_as_crypt' => ['rOVL0k/supDAY', 'rOVL0k/supDAY'],
            'fail_plain' => ['password', 'other', false],
            'fail_md5' => ['password', '$apr1$aGwevNmX$4WQ0UxE4TzhoaX6QkeBJJ0', false],
            'fail_sha1' => ['password', '{SHA}W6ph5Mm5Zz8GgiULbPgzG37mj9g=', false],
            'fail_crypt' => ['password', 'rOVL0z/supDAY', false],
            'fail_bcrypt' => ['pass1234', '$2y$05$skTPtV45nT7GyeUIMrmUMuI8iqFPcvROODoTI2oUXTCVaebvdeZmq', false],
        ];
    }

    /**
     * covers ::sha1
     * @dataProvider providerSha1
     */
    public function testSha1(string $password, string $expected): void
    {
        $this->assertSame($expected, Crypt::sha1($password));
    }

    public static function providerSha1(): array
    {
        return [
            ['password', '{SHA}W6ph5Mm5Pz8GgiULbPgzG37mj9g='],
            ['my-password-long-long-long', '{SHA}79Dt2/mp7D80ZQdLIOxJScmlttU='],
        ];
    }

    /**
     * covers ::cryptVerify
     * @dataProvider providerCryptVerify
     */
    public function testCryptVerify(string $password, string $hash, bool $expected = true): void
    {
        $this->assertSame($expected, Crypt::cryptVerify($password, $hash));
    }

    public static function providerCryptVerify(): array
    {
        return [
            ['password', 'rOVL0k/supDAY'],
            ['password', 'l2eNr.2J4AvwE'],
            ['long-long-password', 'pviOZdeKeC.vU'],
            ['long-long', 'pviOZdeKeC.vU'],
            ['long-lon', 'pviOZdeKeC.vU'],
            ['long-lo', 'pviOZdeKeC.vU', false],
            ['password', 'l3eNr.2J4AvwE', false],
        ];
    }

    /**
     * covers ::cryptHash
     * @dataProvider providerCryptHash
     */
    public function testCryptHash(string $password): void
    {
        $hash = Crypt::cryptHash($password);
        $this->assertIsString($hash);
        $this->assertTrue(Crypt::cryptVerify($password, $hash));
    }

    public static function providerCryptHash(): array
    {
        return [
            ['password'],
            ['my-password'],
            ['qwe-rty'],
        ];
    }
}
