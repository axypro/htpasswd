<?php
/**
 * @package axy\htpasswd
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\htpasswd\io;

/**
 * The interface of i/o htpasswd "file"
 */
interface IFile
{
    /**
     * @return string
     */
    public function load();

    /**
     * @param string $content
     */
    public function save($content);
}
