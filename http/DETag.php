<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

use app\decibel\http\request\DRequest;

/**
 * Provides functionality for using ETags.
 *
 * @author    Timothy de Paris
 */
class DETag
{
    /**
     * Generates an ETag from the provided information.
     *
     * @param    int $filesize The size of the file.
     * @param    int $mtime    The last modified timestamp of the file.
     *
     * @return    string
     */
    public static function generate($filesize, $mtime)
    {
        $mtime = base_convert(str_pad($mtime, 16, "0"), 10, 16);

        return sprintf('%x-%s', $filesize, $mtime);
    }

    /**
     * Checks if the specified ETag was provided in the request headers
     * and implements appropriate caching behaviour.
     *
     * If no ETag is available in the request header (i.e. If-None-Match header
     * is not present), nothing will be done.
     *
     * If the specified ETag is present, the function will return
     * a 304 Not Modified header and end script execution.
     *
     * If the ETags do not match, the specified ETag will be sent back
     * in the response headers.
     *
     * @param    string $eTag The ETag.
     *
     * @return    null
     * @throws    DNotModified    If the ETags match.
     */
    public static function match($eTag)
    {
        if (!$eTag) {
            return null;
        }
        $request = DRequest::load();
        $headers = $request->getHeaders();
        $requestETag = trim((string)$headers->getHeader('If-None-Match'), '"');
        // Matching ETags.
        if ($requestETag === $eTag) {
            throw new DNotModified();
        }
        header("ETag: \"{$eTag}\"");
    }
}
