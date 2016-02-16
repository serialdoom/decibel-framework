# How to contribute to Decibel Core

## Report a bug

**Ensure the bug was not already reported** by searching on GitHub under 
[Issues](https://github.com/decibeltechnology/decibel-framework/issues).

If you're unable to find an open issue addressing the problem, 
[open a new one](https://github.com/decibeltechnology/decibel-framework/issues/new). 
Be sure to include a title and clear description, as much relevant information 
as possible, and a code sample or a Unit Test demonstrating the 
expected behavior that is not occurring.

## Submitting a Patch

The recommended way to submit a patch or add a new feature is to submit a [pull
request](https://help.github.com/articles/using-pull-requests/).

### One patch per one bug / feature

Avoid submitting patches that mix together multiple features or bug fixes into
a singular changeset. It's significantly easier to review and understand a patch
that is limited to a single purpose.

### Minimum PHP Version

The minimal supported version for Decibel Core 7 is PHP 5.5.x, please keep
this version requirement in mind when contributing a patch to the framework.

## Running the Tests

### Before Running the Tests

To run the Decibel Core tests, install the external requirements used during the
tests. To do so; [install Composer](https://getcomposer.org/download/) and 
execute the following:

```shell
$ composer update
```
### Running the Tests
 
After installing the requirements, run the test suite from the Decibel Core root
directory using the following command:

```shell
$ vendor/bin/phpunit
```

## Coding Style

Decibel Core follows the 
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) 
coding standard.
