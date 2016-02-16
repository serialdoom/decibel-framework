<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\stream;

/**
 * The {@link DOutputStream} class provides a write-only stream that can be
 * used to write content to the output buffer.
 *
 * @author        Timothy de Paris
 */
class DOutputStream extends DStream
    implements DHttpStream
{
    /**
     * The stream handle
     *
     * @var        resource
     */
    private $handle;

    /**
     * Creates a new {@link DOutputStream}.
     *
     * @return    static
     */
    public function __construct()
    {
        $this->handle = fopen('php://output', 'w');
    }

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
        if (ftruncate($this->handle, 0) === false) {
            throw new DStreamWriteException($this, 'Unable to erase stream content.');
        }
    }

    /**
     * Flushes the content of the stream to the client.
     *
     * @return    void
     */
    public function flush()
    {
        ob_end_flush();
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
            header("{$header}: {$value}");
        } else {
            header($header);
        }
    }

    /**
     * Bulk action to set multiple headers to be sent to the client
     *
     * @param array $headers
     *
     * @return void
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
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
        fwrite($this->handle, $data);
    }
}
