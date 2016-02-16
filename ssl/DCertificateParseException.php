<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

/**
 * Handles an exception occurring when parsing of a certificate fails.
 *
 * @section        why Why Would I Use It?
 *
 * This exception is thrown by the {@link DCertificate::parse()}
 * and {@link DCertificate::open()} methods when parsing of a certificate fails.
 *
 * @section        how How Do I Use It?
 *
 * This exception should be caught using a <code>try ... catch</code> block
 * around any execution of {@link DCertificate::parse()}
 * and {@link DCertificate::open()}.
 *
 * @subsection     example Examples
 *
 * The following example handles a {@link DCertificateParseException}.
 *
 * @code
 * use app\decibel\ssl\DCertificate;
 * use app\decibel\ssl\DCertificateParseException;
 *
 * try {
 *    $cert = DCertificate::parse($pem);
 * } catch (DCertificateParseException $e) {
 *    debug('Unable to parse certificate');
 * }
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        ssl_exceptions
 */
class DCertificateParseException extends DSslException
{
}
