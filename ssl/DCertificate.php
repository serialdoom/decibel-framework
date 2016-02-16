<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\stream\DReadableStream;
use app\decibel\stream\DStreamWriteException;
use app\decibel\stream\DWritableStream;

/**
 * Provides functionality for working with SSL certificates.
 *
 * @author    Timothy de Paris
 */
class DCertificate extends DSslResource
{
    /**
     * Timestamp until which the certificate is valid.
     *
     * @var        int
     */
    protected $validity;
    /**
     * Certificate subject.
     *
     * @var        DDistinguishedName
     */
    protected $subject;
    /**
     * Certificate issuer.
     *
     * @var        DDistinguishedName
     */
    protected $issuer;

    /**
     * Creates a new certificate.
     *
     * @param    resource $certificate The certificate resource.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the provided certificate
     *                                            is not a valid resource.
     */
    public function __construct($certificate)
    {
        // Check that a valid resource is provided.
        if (!is_resource($certificate)) {
            throw new DInvalidParameterValueException(
                'certificate',
                array(__CLASS__, __FUNCTION__),
                'A valid certificate resource.'
            );
        }
        $this->resource = $certificate;
        $data = openssl_x509_parse($certificate);
        $this->validity = $data['validTo_time_t'];
        $this->issuer = DDistinguishedName::createFromSubject($data['issuer']);
        $this->subject = DDistinguishedName::createFromSubject($data['subject']);
    }

    /**
     * Frees memory associated with resources for this certificate.
     *
     * @return    void
     */
    public function __destruct()
    {
        if (is_resource($this->resource)) {
            openssl_x509_free($this->resource);
            unset($this->resource);
        }
    }

    /**
     * Provides debugging output for this object.
     *
     * This function must return a multi-dimensional array containing
     * key/value pairs of object properties to be debugged.
     *
     * @return    array
     */
    public function generateDebug()
    {
        return array(
            'subject'     => $this->subject,
            'validity'    => $this->validity,
            'issuer'      => $this->issuer,
            'certificate' => $this->export(),
        );
    }

    /**
     * Signs a certificate signing request to generate a certificate.
     *
     * @param    DSigningRequest $signingRequest  The certificate signing request
     *                                            to generate the certificate from.
     * @param    DPrivateKey     $privateKey      The private key of the certificate
     *                                            authority.
     * @param    int             $validity        The number of days for which the
     *                                            certificate will remain valid.
     * @param    DCertificate    $ca              The certificate authority that
     *                                            will sign the certificate. If not
     *                                            provided, a self-signed certificate
     *                                            will be generated.
     *
     * @return    static
     */
    public static function generate(DSigningRequest $signingRequest,
                                    DPrivateKey $privateKey, $validity = 365, DCertificate $ca = null)
    {
        if ($ca !== null) {
            $caResource = $ca->getResource();
        } else {
            $caResource = null;
        }

        // Generate using static so that overriding classes are honoured.
        return new static(openssl_csr_sign(
                              $signingRequest->getResource(),
                              $caResource,
                              $privateKey->getResource(),
                              $validity
                          ));
    }

    /**
     * Loads a PEM encoded certificate from the provided stream.
     *
     * @param    DReadableStream $stream  Stream from which to read the PEM
     *                                    encoded certificate file.
     *
     * @return    DCertificate
     * @throws    DCertificateParseException        If the provided stream does not
     *                                            contain a valid PEM encoded
     *                                            certificate.
     */
    public static function open(DReadableStream $stream)
    {
        return DCertificate::parse(
            $stream->read()
        );
    }

    /**
     * Returns the PEM encoded certificate.
     *
     * @return    string
     */
    public function export()
    {
        $certificate = null;
        openssl_x509_export($this->resource, $certificate);

        return $certificate;
    }

    /**
     * Returns the issuer of the certificate.
     *
     * @return    DDistinguishedName    Representation of the issuer
     *                                of this certificate.
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * Returns the subject of the certificate.
     *
     * @return    DDistinguishedName    Representation of the subject
     *                                of this certificate.
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Returns the validity of the certificate.
     *
     * @return    int        UNIX timestamp representing the certificate expiry time.
     */
    public function getValidity()
    {
        return $this->validity;
    }

    /**
     * Determines if this certificate was self-signed.
     *
     * @return    bool
     */
    public function isSelfSigned()
    {
        $issuer = $this->issuer->getSubject();
        $subject = $this->subject->getSubject();

        return ($issuer === $subject);
    }

    /**
     * Parses a PEM formatted certificate.
     *
     * @param    string $pem The PEM formatted certificate.
     *
     * @return    static
     * @throws    DCertificateParseException    If the provided value is not
     *                                        a valid PEM encoded certificate.
     */
    public static function parse($pem)
    {
        // Parse the certificate.
        try {
            return new static(openssl_x509_read($pem));
            // Check that parsing was successful.
        } catch (DInvalidParameterValueException $exception) {
            throw new DCertificateParseException();
        }
    }

    /**
     * Saves the PEM encoded certificate to a file.
     *
     * @param    DWritableStream $stream Stream to write the data to.
     *
     * @return    static
     * @throws    DStreamWriteException    If the data could not be written.
     */
    public function save(DWritableStream $stream)
    {
        $stream->write(
            $this->export()
        );

        return $this;
    }
}
