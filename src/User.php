<?php

declare(strict_types=1);

namespace axy\htpasswd;

/** User in htpasswd file */
class User
{
    public function __construct(public readonly string $name, private ?string $hash = null)
    {
    }

    /** Returns the content of a htpasswd file line */
    public function getFileLine(): string
    {
        return "{$this->name}:{$this->hash}";
    }

    /** Checks a password for the user */
    public function verify(string $password): bool
    {
        return Crypt::verify($password, $this->hash);
    }

    /** Sets the password for the user */
    public function setPassword(
        string $password,
        string $algorithm = PasswordFile::ALG_MD5,
        ?array $options = null,
    ): void {
        $this->hash = Crypt::hash($password, $algorithm, $options);
    }

    public function __toString(): string
    {
        return $this->getFileLine();
    }

    /** Load n user from the file line */
    public static function loadFromFileLine(string $line): ?self
    {
        $line = explode(':', $line, 2);
        if (count($line) !== 2) {
            return null;
        }
        return new self($line[0], $line[1]);
    }
}
