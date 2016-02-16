<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

use app\decibel\http\debug\DMalformedUrlException;
use app\decibel\http\DUrl;

/**
 * Parses string URLs and creates {@link DUrl} objects.
 *
 * @author        Timothy de Paris
 */
class DUrlParser
{
    /**
     * 'fragment' array index for PHP parse_url() function.
     *
     * @var        string
     */
    const PHP_PARSE_URL_FRAGMENT = 'fragment';
    /**
     * 'host' array index for PHP parse_url() function.
     *
     * @var        string
     */
    const PHP_PARSE_URL_HOST = 'host';
    /**
     * 'path' array index for PHP parse_url() function.
     *
     * @var        string
     */
    const PHP_PARSE_URL_PATH = 'path';
    /**
     * 'port' array index for PHP parse_url() function.
     *
     * @var        string
     */
    const PHP_PARSE_URL_PORT = 'port';
    /**
     * 'query' array index for PHP parse_url() function.
     *
     * @var        string
     */
    const PHP_PARSE_URL_QUERY = 'query';
    /**
     * 'scheme' array index for PHP parse_url() function.
     *
     * @var        string
     */
    const PHP_PARSE_URL_SCHEME = 'scheme';

    /**
     * Creates a new {@link DUrl} object by parsing a string representation of a URL.
     *
     * @param    string  $urlString   URL to parse.
     * @param    boolean $normalise   If set to <code>true</code>, normalisation of the URL
     *                                will occure during parsing (for example, adding
     *                                a trailing slash).
     *
     * @return    DUrl    Representation of the provided URL, or <code>null</code>
     *                    if the provided URL could not be parsed.
     * @throws    DMalformedUrlException    If an invalid URL value is provided.
     */
    public static function parse($urlString, $normalise = true)
    {
        $matches = parse_url($urlString);
        if ($matches === false) {
            throw new DMalformedUrlException($urlString);
        }
        set_default($matches[ self::PHP_PARSE_URL_SCHEME ], null);    // protocol
        set_default($matches[ self::PHP_PARSE_URL_HOST ], null);        // hostname
        set_default($matches[ self::PHP_PARSE_URL_PORT ], null);        // port
        set_default($matches[ self::PHP_PARSE_URL_PATH ], '/');        // uri
        set_default($matches[ self::PHP_PARSE_URL_QUERY ], '');        // queryParameters
        set_default($matches[ self::PHP_PARSE_URL_FRAGMENT ], null);    // fragment
        $url = new DUrl($matches[ self::PHP_PARSE_URL_PATH ], $normalise);
        $url->setProtocol($matches[ self::PHP_PARSE_URL_SCHEME ]);
        $url->setHostname($matches[ self::PHP_PARSE_URL_HOST ]);
        $url->setPort($matches[ self::PHP_PARSE_URL_PORT ]);
        $url->setFragment($matches[ self::PHP_PARSE_URL_FRAGMENT ]);
        $queryParameters = null;
        parse_str($matches[ self::PHP_PARSE_URL_QUERY ], $queryParameters);
        $url->setQueryParameters($queryParameters);

        return $url;
    }
}
