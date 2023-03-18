<?php

declare(strict_types=1);

namespace axy\htpasswd;

use axy\crypt\APR1;
use axy\crypt\BCrypt;

/** Hashes and verifies passwords */
class Crypt
{
    /** Hashes a password */
    public static function hash(
        string $password,
        string $algorithm = PasswordFile::ALG_MD5,
        array $options = null,
    ): ?string {
        if ($options === null) {
            $options = [];
        }
        switch ($algorithm) {
            case PasswordFile::ALG_MD5:
                return APR1::hash($password);
            case PasswordFile::ALG_BCRYPT:
                $cost = isset($options['cost']) ? $options['cost'] : null;
                return BCrypt::hash($password, $cost);
            case PasswordFile::ALG_SHA1:
                return self::sha1($password);
            case PasswordFile::ALG_CRYPT:
                return self::cryptHash($password);
            case PasswordFile::ALG_PLAIN:
                return $password;
        }
        return null;
    }

    /** Checks if a password matches a hash */
    public static function verify(string $password, string $hash): bool
    {
        if ($password === $hash) {
            return true;
        }
        if (APR1::verify($password, $hash)) {
            return true;
        }
        if ($hash === self::sha1($password)) {
            return true;
        }
        if (self::cryptVerify($password, $hash)) {
            return true;
        }
        if (str_starts_with($hash, '$2')) {
            if (BCrypt::verify($password, $hash)) {
                return true;
            }
        }
        return false;
    }

    /** Hashes by SHA-1 (Apache version) */
    public static function sha1(string $password): string
    {
        return '{SHA}' . base64_encode(sha1($password, true));
    }

    /** Verifies a hash of CRYPT algorithm */
    public static function cryptVerify(string $password, string $hash): bool
    {
        $salt = substr($hash, 0, 2);
        try {
            // PHP 7 throws exception for invalid salt
            $actual = crypt($password, $salt);
        } catch (\Exception $e) {
            return false;
        }
        return ($actual === $hash);
    }

    /** Hashes a password using CRYPT algorithm */
    public static function cryptHash(string $password): string
    {
        $salt = substr(base64_encode(chr(mt_rand(0, 255))), 0, 2);
        $salt = str_replace('+', '.', $salt);
        return crypt($password, $salt);
    }
}
