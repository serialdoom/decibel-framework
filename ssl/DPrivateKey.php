<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\ssl\DPrivateKeyGenerationException;
use app\decibel\ssl\DPrivateKeyParseException;
use app\decibel\stream\DReadableStream;
use app\decibel\stream\DStreamReadException;
use app\decibel\stream\DStreamWriteException;
use app\decibel\stream\DWritableStream;

/**
 * Provides functionality for working with SSL private keys.
 *
 * @author    Timothy de Paris
 */
class DPrivateKey extends DSslResource
{
    /**
     * Creates a new private key.
     *
     * @param    resource $privateKey The private key resource.
     *
     * @return    DPrivateKey
     * @throws    DInvalidParameterValueException    If the provided private key
     *                                            is not a valid resource.
     */
    protected function __construct($privateKey)
    {
        // Check that a valid resource is provided.
        if (!is_resource($privateKey)) {
            throw new DInvalidParameterValueException(
                'privateKey',
                array(__CLASS__, __FUNCTION__),
                'A valid private key resource.'
            );
        }
        $this->resource = $privateKey;
    }

    /**
     * Frees memory associated with resources for this private key.
     *
     * @return    void
     */
    public function __destruct()
    {
        if (is_resource($this->resource)) {
            openssl_free_key($this->resource);
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
            'privateKey' => $this->export(),
        );
    }

    /**
     * Generates a new private key.
     *
     * @param    int $strength    Number of bits used to generate the private key.
     *                            This must be at least 384 bits, however a minimum
     *                            of 2,048 bits is recommended.
     *
     * @return    static
     * @throws    DPrivateKeyGenerationException    If the private key
     *                                            cannot be generated.
     * @throws    DSslConfigurationException        If SSL has not been correctly
     *                                            configured on this server.
     * @throws    DInvalidParameterValueException If a provided parameter
     *                                            value is invalid.
     */
    public static function generate($strength = 2048)
    {
        if (!is_numeric($strength) || $strength < 384) {
            throw new DInvalidParameterValueException(
                'strength',
                array(__CLASS__, __FUNCTION__),
                'An integer greater than 384'
            );
        }
        // Clear any existing OpenSSL error messages.
        DSslException::clearOpenSslErrors();
        // Generate a private key.
        try {
            return new static(openssl_pkey_new(array(
                                                   'private_key_bits' => $strength,
                                                   'digest_alg'       => 'sha256',
                                               )));
        } catch (DInvalidParameterValueException $exception) {
            $default = new DPrivateKeyGenerationException();
            throw DSslException::getOpenSslException($default);
        }
    }

    /**
     * Loads a PEM encoded private key from the file system.
     *
     * @param    DReadableStream $stream      Stream containing the PEM encoded
     *                                        private key data.
     * @param    string          $passphrase  The passphrase to use if the PEM
     *                                        data is protected.
     *
     * @return    static
     * @throws    DStreamReadException            If the data could not be read.
     * @throws    DPrivateKeyParseException        If the provided stream does not
     *                                            contain a valid PEM encoded
     *                                            private key.
     */
    public static function open(DReadableStream $stream, $passphrase = null)
    {
        return static::parse(
            $stream->read(),
            $passphrase
        );
    }

    /**
     * Parses a PEM formatted private key.
     *
     * @param    string $pem        The PEM formatted private key.
     * @param    string $passphrase The passphrase to use if the PEM is protected.
     *
     * @return    static
     * @throws    DPrivateKeyParseException        If the provided value is not
     *                                            a valid PEM encoded private key.
     */
    public static function parse($pem, $passphrase = null)
    {
        // Parse the private key.
        try {
            return new static(openssl_pkey_get_private(
                                  $pem,
                                  $passphrase
                              ));
            // Check that parsing was successful.
        } catch (DInvalidParameterValueException $exception) {
            throw new DPrivateKeyParseException();
        }
    }

    /**
     * Decrypts the provided data with this private key.
     *
     * @param    string $encrypted The data to decrypt.
     *
     * @return    string
     */
    public function decrypt($encrypted)
    {
        $data = null;
        openssl_private_decrypt($encrypted, $data, $this->resource);

        return $data;
    }

    /**
     * Encrypts the provided data with this private key.
     *
     * @param    string $data The data to encrypt.
     *
     * @return    string
     */
    public function encrypt($data)
    {
        $encrypted = null;
        openssl_private_encrypt($data, $encrypted, $this->resource);

        return $encrypted;
    }

    /**
     * Returns the PEM encoded private key.
     *
     * @param    string $passphrase   If provided, the returned private key will
     *                                be protected with this passphrase.
     *
     * @return    string
     */
    public function export($passphrase = null)
    {
        $privateKey = null;
        openssl_pkey_export($this->resource, $privateKey, $passphrase);

        return $privateKey;
    }

    /**
     * Returns the number of bits in this private key.
     *
     * @return    int
     */
    public function getBits()
    {
        $details = openssl_pkey_get_details($this->resource);

        return $details['bits'];
    }

    /**
     * Returns the corresponding public key for this private key.
     *
     * @return    DPublicKey
     */
    public function getPublicKey()
    {
        $details = openssl_pkey_get_details($this->resource);

        return new DPublicKey(openssl_get_publickey($details['key']));
    }

    /**
     * Saves the PEM encoded private key to the filesystem.
     *
     * @param    DWritableStream $stream      Stream to write the data to.
     * @param    string          $passphrase  If provided, the saved private
     *                                        key will be protected with this
     *                                        passphrase.
     *
     * @return    static
     * @throws    DStreamWriteException    If the data could not be written.
     */
    public function save(DWritableStream $stream, $passphrase = null)
    {
        $stream->write(
            $this->export($passphrase)
        );

        return $this;
    }

    /**
     * Generates a signature for the provided data using this private key.
     *
     * @param    string $data The data to generate a signature for.
     *
     * @return    string
     */
    public function sign($data)
    {
        $signature = null;
        openssl_sign($data, $signature, $this->resource);

        return $signature;
    }
}
