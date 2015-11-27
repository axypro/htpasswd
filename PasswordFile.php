<?php
/**
 * @package axy\htpasswd
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\htpasswd;

use axy\htpasswd\io\IFile;
use axy\htpasswd\io\Real;
use axy\htpasswd\errors\InvalidFileFormat;

/**
 * Htpasswd file wrapper
 */
class PasswordFile
{
    const ALG_MD5 = 'md5';
    const ALG_SHA1 = 'sha1';
    const ALG_PLAIN = 'plain';

    /**
     * The constructor
     *
     * @param mixed $filename
     */
    public function __construct($filename)
    {
        if ($filename instanceof IFile) {
            $this->io = $filename;
        } else {
            $this->filename = $filename;
            $this->io = new Real($filename);
        }
    }

    /**
     * Returns the file name
     */
    public function getFileName()
    {
        return $this->filename;
    }

    /**
     * Sets the password for a user
     *
     * @param string $user
     * @param string $password
     * @param string $algorithm [optional]
     * @return bool
     *         the user has been created
     * @throws \axy\htpasswd\errors\InvalidFileFormat
     */
    public function setPassword($user, $password, $algorithm = self::ALG_MD5)
    {
        $this->load();
        if (isset($this->users[$user])) {
            $this->users[$user]->setPassword($password, $algorithm);
            return false;
        }
        $oUser = new User($user);
        $oUser->setPassword($password, $algorithm);
        $this->users[$user] = $oUser;
        return true;
    }

    /**
     * Saves the user list to the file
     *
     * @throws \axy\htpasswd\errors\InvalidFileFormat
     */
    public function save()
    {
        $this->io->save($this->getContent());
    }

    /**
     * Returns the content of the file
     *
     * @throws \axy\htpasswd\errors\InvalidFileFormat
     */
    public function getContent()
    {
        $this->load();
        $content = [];
        foreach ($this->users as $user) {
            $content[] = $user->getFileLine().PHP_EOL;
        }
        return implode('', $content);
    }

    /**
     * Checks if a user exist in the file
     *
     * @param string $user
     * @return bool
     */
    public function isUserExist($user)
    {
        $this->load();
        return isset($this->users[$user]);
    }

    /**
     * Verifies a password
     *
     * @param string $user
     * @param string $password
     * @return bool
     */
    public function verify($user, $password)
    {
        $this->load();
        if (!isset($this->users[$user])) {
            return false;
        }
        return $this->users[$user]->verify($password);
    }

    /**
     * Loads a user list from file
     *
     * @throws \axy\htpasswd\errors\InvalidFileFormat
     */
    private function load()
    {
        if ($this->users) {
            return;
        }
        $content = $this->io->load();
        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if ($line !== '') {
                $user = User::loadFromFileLine($line);
                if ($user === null) {
                    return $this->invalid();
                }
                $this->users[$user->getName()] = $user;
            }
        }
    }

    /**
     * @throws \axy\htpasswd\errors\InvalidFileFormat
     */
    private function invalid()
    {
        $filename = $this->filename;
        if (!is_string($filename)) {
            $filename = null;
        }
        throw new InvalidFileFormat($filename);
    }

    /**
     * @var string
     */
    private $filename;

    /**
     * @var \axy\htpasswd\io\IFile
     */
    private $io;

    /**
     * @var \axy\htpasswd\User[] (nick => object)
     */
    private $users;
}
