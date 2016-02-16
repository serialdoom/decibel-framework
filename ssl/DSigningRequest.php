<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

use app\decibel\ssl\DDistinguishedName;
use app\decibel\ssl\DPrivateKey;
use app\decibel\stream\DStreamWriteException;
use app\decibel\stream\DWritableStream;

/**
 * Provides functionality for working with SSL
 * certificate signing requests (CSR).
 *
 * @author    Timothy de Paris
 */
class DSigningRequest extends DSslResource
{
    /**
     * Creates a new certificate.
     *
     * @return    DSigningRequest
     */
    protected function __construct()
    {
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
            'signingRequest' => $this->export(),
        );
    }

    /**
     * Generates a new private key.
     *
     * @param    DDistinguishedName $dn           The distinguished name to use.
     * @param    DPrivateKey        $privateKey   The private key used
     *                                            to sign the CSR.
     *
     * @return    DSigningRequest
     * @throws    DSigningRequestGenerationException    If the signing request
     *                                                cannot be generated.
     */
    public static function generate(DDistinguishedName $dn,
                                    DPrivateKey $privateKey)
    {
        $csr = new DSigningRequest();
        // Default configuration and options for CSR generation.
        $config = array(
            'digest_alg' => 'sha256',
        );
        // Convert DN to an array.
        // @todo Convert DN to use fields and change this to getFieldValues()
        $dnArray = $dn->toArray();
        unset($dnArray['_qualifiedName']);
        // Generate a CSR.
        // Note: Pasing an empty options array (not currently done) will cause an issue
        // See https://bugs.php.net/bug.php?id=61401
        $privateKeyResource = $privateKey->getResource();
        $csr->resource = openssl_csr_new(
            $dnArray,
            $privateKeyResource,
            $config
        );
        // Check that generation was successful.
        if (!is_resource($csr->resource)) {
            throw new DSigningRequestGenerationException($dn->commonName);
        }

        return $csr;
    }

    /**
     * Returns the public key for this signing request.
     *
     * @return    DPublicKey
     */
    public function getPublicKey()
    {
        return new DPublicKey(openssl_csr_get_public_key(
                                  $this->resource
                              ));
    }

    /**
     * Returns the signing request subject.
     *
     * @return    array    Array with the keys CN, OU, etc.
     */
    public function getSubject()
    {
        return openssl_csr_get_subject($this->resource);
    }

    /**
     * Returns the PEM encoded certificate signing request (CSR).
     *
     * @return    string
     */
    public function export()
    {
        $signingRequest = null;
        openssl_csr_export(
            $this->resource,
            $signingRequest
        );

        return $signingRequest;
    }

    /**
     * Saves the PEM encoded certificate signing request (CSR) to a file.
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
