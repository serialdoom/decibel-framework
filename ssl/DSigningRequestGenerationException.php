<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

/**
 * Handles an exception occurring when generation of a certificate signing
 * request fails.
 *
 * @section        why Why Would I Use It?
 *
 * This exception is thrown by the {@link DSigningRequest::generate()} method
 * when geneation of a signing request fails.
 *
 * @section        how How Do I Use It?
 *
 * This exception should be caught using a <code>try ... catch</code> block
 * around any execution of {@link DSigningRequest::generate()}.
 *
 * @subsection     example Examples
 *
 * The following example handles a {@link DSigningRequestGenerationException}.
 *
 * @code
 * use app\decibel\ssl\DSigningRequest;
 * use app\decibel\ssl\DSigningRequestGenerationException;
 *
 * try {
 *    $csr = DSigningRequest::generate($dn, $key);
 * } catch (DSigningRequestGenerationException $e) {
 *    debug('Unable to generate CSR');
 * }
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        ssl_exceptions
 */
class DSigningRequestGenerationException extends DSslException
{
    /**
     * Creates a new {@link DSigningRequestGenerationException}.
     *
     * @param    string $commonName Common name of the CSR.
     *
     * @return    static
     */
    public function __construct($commonName)
    {
        parent::__construct(array(
                                'commonName' => $commonName,
                            ));
    }
}
