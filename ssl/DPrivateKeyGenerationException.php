<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

/**
 * Handles an exception occurring when generation of a private key fails.
 *
 * @section        why Why Would I Use It?
 *
 * This exception is thrown by the {@link DPrivateKey::generate()} method
 * when generation of a private key fails.
 *
 * @section        how How Do I Use It?
 *
 * This exception should be caught using a <code>try ... catch</code> block
 * around any execution of {@link DPrivateKey::generate()}.
 *
 * @subsection     example Examples
 *
 * The following example handles a {@link DPrivateKeyGenerationException}.
 *
 * @code
 * use app\decibel\ssl\DPrivateKey;
 * use app\decibel\ssl\DPrivateKeyGenerationException;
 *
 * try {
 *    $csr = DPrivateKey::generate();
 * } catch (DPrivateKeyGenerationException $e) {
 *    debug('Unable to generate private key');
 * }
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        ssl_exceptions
 */
class DPrivateKeyGenerationException extends DSslException
{
}
