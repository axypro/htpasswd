# axy\htpasswd

Working with htpasswd file (PHP).

[![Latest Stable Version](https://img.shields.io/packagist/v/axy/htpasswd.svg?style=flat-square)](https://packagist.org/packages/axy/htpasswd)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg?style=flat-square)](https://php.net/)
[![Tests](https://github.com/axypro/htpasswd/actions/workflows/test.yml/badge.svg)](https://github.com/axypro/htpasswd/actions/workflows/test.yml)
[![Coverage Status](https://coveralls.io/repos/github/axypro/htpasswd/badge.svg?branch=master)](https://coveralls.io/github/axypro/htpasswd?branch=master)
[![License](https://poser.pugx.org/axy/htpasswd/license)](LICENSE)

### Documentation

The library provides program API for manipulation with htpasswd file
(for console utility see [axypro/htpasswd-cli](https://github.com/axypro/htpasswd-cli)).

```php
use axy\htpasswd\PasswordFile;

$file = new PasswordFile('/path/to/.htpasswd');
$file->setPassword('nick', 'password');
$file->setPassword('john', '123456');
$file->save();
```

Currently supported the following algorithms (constants of `PasswordFile::*`):

 * `ALG_MD5`: Apache APR1-MD5 algorithm (by default)
 * `ALG_BCrypt`: Blowfish
 * `ALG_SHA1`: SHA-1
 * `ALG_CRYPT`: crypt (unix)
 * `ALG_PLAIN`: Plain text (not supported of servers on some platforms).

##### `__construct([string $filename])`

The constructor takes the name of a htpasswd file.

Or `NULL`: analogue of the option `-n` of the console utility:

```php
$file = new PasswordFile();
$file->setPassword('nick', 'password');
$file->getContent(); // out of the "file" content

$file->save(); // Exception FileNotSpecified
```

##### `setPassword(string $user, string $password [, string $algorithm, [array $options]): bool`

Sets the password `$password` for a user `$user`.
For hashing uses `$algorithm` (by default Apache MD5).

`$options` is an array of options for hashing.
Only `cost` for BCrypt supported (integer in the range 4 to 31)

Returns `TRUE` if a new user has been created.
And `FALSE` if has been changed the password of an existing user.

##### `remove(string $user): bool`

Removes a user from the file.
Returns `TRUE` is the user has been removed.
And `FALSE` if the user was not found.

##### `verify(string $user, string $password): bool`

Returns `TRUE` if a `$user` exists and has `$password` as the password.

##### `isUserExist(string $user): bool`

Returns `TRUE` is a user exists in the file.

```php
if (!$file->isUserExist('john')) {
    echo 'John? I do not known you.';
    exit();
}
if (!$file->verify('john', 'password')) {
    echo 'You are not John! You are an impostor!';
    exit();
}
echo 'Hello, John';
```

##### `getContent(void): string`

Returns the file content (without saving).

##### `save(void): void`

Saves the content to the file (if it is specified).

In contrast, from the utility `htpasswd` (see the option `-c`) the existing file always changing (not overwritten).
Nonexistent file is created.

##### `setFileName(string $filename): void`

Sets a new filename. The content of the old file will be loaded and saved to the new (after `save()`).

##### `getFileName(void): string`

Returns the current specified file name.

### Exceptions

In the `axy\htpasswd\errors` namespace.

* `InvalidFileFormat`: a password file has invalid format.
* `FileNotSpecified`: throws from `save()` if the file is not specified in the constructor.
