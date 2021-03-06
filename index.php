<?php
/**
 * Working with htpasswd file
 *
 * @package axy\htpasswd
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 * @license https://raw.github.com/axypro/htpasswd/master/LICENSE MIT
 * @link https://github.com/axypro/htpasswd repository
 * @link https://packagist.org/packages/axy/htpasswd composer package
 * @uses PHP5.4+
 */

namespace axy\htpasswd;

if (!is_file(__DIR__.'/vendor/autoload.php')) {
    throw new \LogicException('Please: composer install');
}

require_once(__DIR__.'/vendor/autoload.php');
