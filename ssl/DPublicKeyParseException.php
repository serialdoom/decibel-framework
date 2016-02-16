<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

/**
 * Handles an exception occurring when parsing of a public key fails.
 *
 * @section        why Why Would I Use It?
 *
 * This exception is thrown by the {@link DPublicKey::parse()}
 * and {@link DPublicKey::open()} methods when parsing of a public key fails.
 *
 * @section        how How Do I Use It?
 *
 * This exception should be caught using a <code>try ... catch</code> block
 * around any execution of {@link DPublicKey::parse()}
 * and {@link DPublicKey::open()}.
 *
 * @subsection     example Examples
 *
 * The following example handles a {@link DPublicKeyParseException}.
 *
 * @code
 * use app\decibel\ssl\DPublicKey;
 * use app\decibel\ssl\DPublicKeyParseException;
 *
 * try {
 *    $csr = DPublicKey::parse($key);
 * } catch (DPublicKeyParseException $e) {
 *    debug('Unable to parse public key');
 * }
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        ssl_exceptions
 */
class DPublicKeyParseException extends DSslException
{
}
