<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\file\DFile;
use app\decibel\file\DFileAccessException;
use app\decibel\file\DFileNotFoundException;
use app\decibel\ssl\DPublicKeyParseException;

/**
 * Provides functionality for working with SSL public keys.
 *
 * @author    Timothy de Paris
 */
class DPublicKey extends DSslResource
{
    /**
     * Creates a new public key.
     *
     * @param    resource $publicKey The public key resource.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the provided public key
     *                                            is not a valid resource.
     */
    public function __construct($publicKey)
    {
        // Check that a valid resource is provided.
        if (!is_resource($publicKey)) {
            throw new DInvalidParameterValueException(
                'publicKey',
                array(__CLASS__, __FUNCTION__),
                'A valid public key resource.'
            );
        }
        $this->resource = $publicKey;
    }

    /**
     * Frees memory associated with resources for this public key.
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
            'publicKey' => $this->export(),
        );
    }

    /**
     * Loads a PEM encoded public key from the file system.
     *
     * @param    string $filename Name of the PEM encoded public key file.
     *
     * @return    static
     * @throws    DFileAccessException        If the user does not have access
     *                                        to the file at the specified path.
     * @throws    DFileNotFoundException        If no file exists at the specified path.
     * @throws    DPublicKeyParseException    If the provided file does not
     *                                        contain a valid PEM encoded
     *                                        public key.
     */
    public static function open($filename)
    {
        $publicKey = new DFile($filename);
        $publicKeyStream = $publicKey->getStream();

        return static::parse(
            $publicKeyStream->read()
        );
    }

    /**
     * Parses a PEM formatted public key.
     *
     * @param    string $pem The PEM formatted public key.
     *
     * @return    static
     * @throws    DPublicKeyParseException        If the provided file does not
     *                                            contain a valid PEM encoded
     *                                            public key.
     */
    public static function parse($pem)
    {
        // Parse the public key.
        try {
            return new static(openssl_pkey_get_public($pem));
            // Check that parsing was successful.
        } catch (DInvalidParameterValueException $exception) {
            throw new DPublicKeyParseException();
        }
    }

    /**
     * Decrypts the provided data with this public key.
     *
     * @param    string $encrypted The data to decrypt.
     *
     * @return    string
     */
    public function decrypt($encrypted)
    {
        $data = null;
        openssl_public_decrypt($encrypted, $data, $this->resource);

        return $data;
    }

    /**
     * Encrypts the provided data with this public key.
     *
     * @param    string $data The data to encrypt.
     *
     * @return    string
     */
    public function encrypt($data)
    {
        $encrypted = null;
        openssl_public_encrypt($data, $encrypted, $this->resource);

        return $encrypted;
    }

    /**
     * Returns the PEM encoded public key.
     *
     * @return    string
     */
    public function export()
    {
        $details = openssl_pkey_get_details($this->resource);

        return $details['key'];
    }

    /**
     * Saves the PEM encoded public key to the filesystem.
     *
     * @param    string $filename Name of the file to save to.
     *
     * @return    static
     */
    public function save($filename)
    {
        file_put_contents($filename, $this->export());

        return $this;
    }

    /**
     * Verifies that the signature for the specified data was generated
     * with the private key corresponding to this public key.
     *
     * @param    string $data      The signed data.
     * @param    string $signature The signature.
     *
     * @return    bool    <code>true</code> if the signature is valid,
     *                    <code>false</code> if not.
     */
    public function verify($data, $signature)
    {
        return (bool)openssl_verify(
            $data,
            $signature,
            $this->resource
        );
    }
}
