<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

/**
 * Handles an exception occurring when parsing of a private key fails.
 *
 * @section        why Why Would I Use It?
 *
 * This exception is thrown by the {@link DPrivateKey::parse()}
 * and {@link DPrivateKey::open()} methods when parsing of a private key fails.
 *
 * @section        how How Do I Use It?
 *
 * This exception should be caught using a <code>try ... catch</code> block
 * around any execution of {@link DPrivateKey::parse()}
 * and {@link DPrivateKey::open()}.
 *
 * @subsection     example Examples
 *
 * The following example handles a {@link DPrivateKeyParseException}.
 *
 * @code
 * use app\decibel\ssl\DPrivateKey;
 * use app\decibel\ssl\DPrivateKeyParseException;
 *
 * try {
 *    $csr = DPrivateKey::parse($key);
 * } catch (DPrivateKeyParseException $e) {
 *    debug('Unable to parse private key');
 * }
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        ssl_exceptions
 */
class DPrivateKeyParseException extends DSslException
{
}
