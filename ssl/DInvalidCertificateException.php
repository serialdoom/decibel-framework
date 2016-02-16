<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

/**
 * Handles an exception occurring when retrieval of a certificate fails.
 *
 * @section        why Why Would I Use It?
 *
 * This exception is thrown by the {@link DCertificateGetter::getCertificate()}
 * methods when retrieval of a certificate fails.
 *
 * @section        how How Do I Use It?
 *
 * This exception should be caught using a <code>try ... catch</code> block
 * around any execution of {@link DCertificateGetter::getCertificate()}.
 *
 * @subsection     example Examples
 *
 * The following example handles a {@link DInvalidCertificateException}.
 *
 * @code
 * use app\decibel\ssl\DCertificateGetter;
 * use app\decibel\ssl\DInvalidCertificateException;
 *
 * try {
 *    $cert = DCertificateGetter::getCertificate('example.com');
 * } catch (DInvalidCertificateException $e) {
 *    debug('Unable to retrieve certificate');
 * }
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        ssl_exceptions
 */
class DInvalidCertificateException extends DSslException
{
    /**
     * Creates a new {@link DInvalidCertificateException}.
     *
     * @param    string $domain Domain certificate is being retrieved from.
     *
     * @return    static
     */
    public function __construct($domain)
    {
        parent::__construct(array(
                                'domain' => $domain,
                            ));
    }
}
