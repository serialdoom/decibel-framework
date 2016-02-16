<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

use app\decibel\rpc\DCurlRequest;
use app\decibel\rpc\debug\DInvalidRemoteProcedureCallException;
use app\decibel\rpc\debug\DRemoteServerRedirectException;
use app\decibel\ssl\DCertificate;

/**
 * Retrieves an SSL Certificate for a domain.
 *
 * @author    Sajid Afzal
 */
class DCertificateGetter
{
    /**
     * Checks the validity of a domain.
     *
     * @return    void
     * @throws    DInvalidRemoteProcedureCallException    If the provided domain does not respond
     *                                                    to an HTTPS request.
     */
    protected static function checkDomain($url)
    {
        // Allow DCurlRequest to throw an exception if the domain is not valid.
        try {
            DCurlRequest::create($url)
                // @todo Implement a method in DCurlRequest to do this.
                        ->setCurlOptions(array(
                                             CURLOPT_SSL_VERIFYPEER => false,
                                             CURLOPT_SSL_VERIFYHOST => false,
                                             CURLOPT_FOLLOWLOCATION => false,
                                         ))
                        ->execute();
            // Ignore redirects, throw any other exception.
        } catch (DRemoteServerRedirectException $exception) {
        }
    }

    /**
     * Signs a certificate signing request to generate a certificate.
     *
     * @note
     * This method will not verify that the certificate is valid
     * for the host that it is retrieved from, nor does it verify
     * that the certificate is authentic (i.e. correctly signed).
     *
     * @param    string $hostname Host to retrieve the certificate from.
     *
     * @return    DCertificate
     * @throws    DInvalidRemoteProcedureCallException    If the provided domain does not respond
     *                                                    to an HTTPS request.
     * @throws    DInvalidCertificateException    If the certificate could not be retrieved.
     */
    public static function getCertificate($hostname)
    {
        $url = "https://{$hostname}/";
        self::checkDomain($url);
        $params = self::getStreamContextParams($url);
        if (isset($params['options']['ssl']['peer_certificate'])) {
            return new DCertificate($params['options']['ssl']['peer_certificate']);
        }
        throw new DInvalidCertificateException($hostname);
    }

    /**
     * Returns parameters for a stream context.
     *
     * @param    string $url
     *
     * @return    array
     * @throws    DInvalidRemoteProcedureCallException    If the provided domain
     *                                                    does not respond to an
     *                                                    HTTPS request.
     */
    protected static function getStreamContextParams($url)
    {
        // Set up configuration for the stream.
        $configuration = stream_context_create(array(
                                                   'ssl' => array(
                                                       'capture_peer_cert' => true,
                                                   ),
                                               ));
        $source = fopen($url, 'rb', false, $configuration);
        // Just in case DCurlRequest couldn't detect the problem...
        if ($source === false) {
            throw DInvalidRemoteProcedureCallException($url);
        }

        return stream_context_get_params($source);
    }
}
