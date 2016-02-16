<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

/**
 * Handles an exception occurring when OpenSSL has not been correctly configured.
 *
 * @section        why Why Would I Use It?
 *
 * This exception is thrown by the {@link DPrivateKey::generate()}
 * method when OpenSSL has not been correctly configured.
 *
 * @section        how How Do I Use It?
 *
 * This exception should be caught using a <code>try ... catch</code> block
 * around any execution of {@link DPrivateKey::generate()}.
 *
 * @subsection     example Examples
 *
 * The following example handles a {@link DSslConfigurationException}.
 *
 * @code
 * use app\decibel\ssl\DPrivateKey;
 * use app\decibel\ssl\DSslConfigurationException;
 *
 * try {
 *    $csr = DPrivateKey::generate();
 * } catch (DSslConfigurationException $e) {
 *    debug('OpenSSL incorrectly configured!');
 * }
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        ssl_exceptions
 */
class DSslConfigurationException extends DSslException
{
    /**
     * Creates a new {@link DSslConfigurationException}.
     *
     * @param    string $message Message explaining the issue.
     *
     * @return    static
     */
    public function __construct($message = null)
    {
        parent::__construct(array(
                                'message' => $message,
                            ));
    }
}
