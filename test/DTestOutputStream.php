<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\test;

use app\decibel\stream\DHttpStream;
use app\decibel\stream\DStream;
use app\decibel\stream\DStreamWriteException;

/**
 * Provides a mock stream for testing HTTP output.
 *
 * @author        Timothy de Paris
 */
class DTestOutputStream extends DStream
    implements DHttpStream
{
    /**
     * The stream output
     *
     * @var        string
     */
    private $output = '';
    /**
     * The stream headers
     *
     * @var        array
     */
    private $headers = array();

    /**
     * Closes the stream.
     *
     * @return    void
     */
    public function close()
    {
        // PHP will close the output stream
        // on termination of the request.
    }

    /**
     * Erases any content existing within the stream.
     *
     * @return    void
     * @throws    DStreamWriteException    If the data could not be erased.
     */
    public function erase()
    {
        throw new DStreamWriteException($this, 'Unable to erase stream content.');
    }

    /**
     * Flushes the content of the stream to the client.
     *
     * @return    void
     */
    public function flush()
    {
        $this->output = '';
    }

    /**
     * Returns the headers sent to this stream.
     *
     * @return    array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns the output sent to this stream.
     *
     * @return    string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Sets the value of a header to be sent to the client.
     *
     * @param    string $header Name of the header.
     * @param    string $value  Header value.
     *
     * @return    void
     */
    public function setHeader($header, $value = null)
    {
        if ($value) {
            $this->headers[] = "{$header}: {$value}";
        } else {
            $this->headers[] = $header;
        }
    }

    /**
     * Writes data to the stream.
     *
     * @param    string $data Data to write to the stream.
     *
     * @return    void
     * @throws    DStreamWriteException    If the data could not be written.
     */
    public function write($data)
    {
        $this->output .= $data;
    }

    /**
     * Sets the value of a header to be sent to the client.
     *
     * @param array $headers array of $header => $value tuples
     *
     */
    public function setHeaders(array $headers)
    {
        // TODO: Implement setHeaders() method.
    }
}
