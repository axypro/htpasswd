<?php

declare(strict_types=1);

namespace axy\htpasswd;

use axy\htpasswd\errors\FileNotSpecified;
use axy\htpasswd\io\IFile;
use axy\htpasswd\io\Real;
use axy\htpasswd\errors\InvalidFileFormat;

/** Htpasswd file wrapper */
class PasswordFile
{
    public const ALG_MD5 = 'md5';
    public const ALG_BCRYPT = 'bcrypt';
    public const ALG_SHA1 = 'sha1';
    public const ALG_CRYPT = 'crypt';
    public const ALG_PLAIN = 'plain';

    public function __construct(string|IFile $filename = null)
    {
        if ($filename instanceof IFile) {
            $this->io = $filename;
        } else {
            $this->filename = $filename;
            $this->io = new Real($filename);
        }
    }

    /** Returns the file name */
    public function getFileName(): ?string
    {
        return $this->filename;
    }

    /**
     * Sets the password for a user
     *
     * @throws InvalidFileFormat
     */
    public function setPassword(
        string $user,
        string $password,
        string $algorithm = self::ALG_MD5,
        ?array $options = null,
    ): bool {
        $this->load();
        $new = !isset($this->users[$user]);
        if ($new) {
            $oUser = new User($user);
            $oUser->setPassword($password, $algorithm, $options);
            $this->users[$user] = $oUser;
        } else {
            $this->users[$user]->setPassword($password, $algorithm, $options);
        }
        return $new;
    }

    /**
     * Saves the user list to the file
     *
     * @throws InvalidFileFormat
     * @throws FileNotSpecified
     */
    public function save(): void
    {
        $this->io->save($this->getContent());
    }

    /**
     * Returns the content of the file
     *
     * @throws InvalidFileFormat
     */
    public function getContent(): string
    {
        $this->load();
        $content = [];
        foreach ($this->users as $user) {
            $content[] = $user->getFileLine() . PHP_EOL;
        }
        return implode('', $content);
    }

    /** Checks if a user exist in the file */
    public function isUserExist(string $user): bool
    {
        $this->load();
        return isset($this->users[$user]);
    }

    /** Verifies a user password */
    public function verify(string $user, string $password): bool
    {
        $this->load();
        if (!isset($this->users[$user])) {
            return false;
        }
        return $this->users[$user]->verify($password);
    }

    /** Removes a user from the file */
    public function remove(string $user): bool
    {
        $this->load();
        if (!isset($this->users[$user])) {
            return false;
        }
        unset($this->users[$user]);
        return true;
    }

    /** Sets a new filename */
    public function setFileName(string $filename): void
    {
        $this->load();
        $this->filename = $filename;
        $this->io = new Real($filename);
    }

    /**
     * Loads a user list from file
     *
     * @throws InvalidFileFormat
     */
    private function load(): void
    {
        if ($this->users) {
            return;
        }
        $content = $this->io->load();
        $this->users = [];
        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if ($line !== '') {
                $user = User::loadFromFileLine($line);
                if ($user === null) {
                    $this->throwInvalid();
                }
                $this->users[$user->name] = $user;
            }
        }
    }

    /** @throws InvalidFileFormat */
    private function throwInvalid(): never
    {
        $filename = $this->filename;
        if (!is_string($filename)) {
            $filename = null;
        }
        throw new InvalidFileFormat($filename);
    }

    private ?string $filename = null;

    private IFile $io;

    /** @var User[] (nick => object) */
    private ?array $users = null;
}
