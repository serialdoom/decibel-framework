<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\stream;

/**
 *
 *
 * @author        Timothy de Paris
 */
class DFileStream extends DStream
    implements DReadableCsvStream, DSeekableStream, DWritableStream
{
    /**
     * Regular expression used to match the BOM (byte order mark).
     *
     * Currently supported encodings are (in left-to-right order within
     * the regular expression):
     * - UTF-8
     * - UTF-16 (BE)
     * - UTF-16 (LE)
     * - UTF-32 (BE)
     * - UTF-32 (LE)
     *
     * @note
     * See http://en.wikipedia.org/wiki/Byte_order_mark for further information
     * about the byte order mark.
     *
     * @var        string
     */
    const REGEX_BOM = '/^(\xef\xbb\xbf|\x00\x00\xfe\xff|\xff\xfe\x00\x00|\xfe\xff|\xff\xfe)/';

    /**
     * Name of the file.
     *
     * @var        string
     */
    protected $filename;

    /**
     * The stream handle, for reading from the file.
     *
     * @var        resource
     */
    private $readHandle;

    /**
     * The stream handle, for writing to the file.
     *
     * @var        resource
     */
    private $writeHandle;

    /**
     * Creates a new {@link DFileStream}.
     *
     * @param    string $filename Name of the file.
     *
     * @return    static
     * @todo    Check that the provided filename exists within the web root
     *            for this installation (see DFile), however it may not exist
     *            in this case to realpath is not the solution.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Returns a string representation of the stream.
     *
     * @note
     * This does not return the content of the stream.
     *
     * @return    string
     */
    public function __toString()
    {
        return get_class($this) . " ({$this->filename})";
    }

    /**
     * Closes the stream.
     *
     * @return    void
     */
    public function close()
    {
        if (is_resource($this->readHandle)) {
            fclose($this->readHandle);
        }
        if (is_resource($this->writeHandle)) {
            fclose($this->writeHandle);
        }
    }

    /**
     * Erases any content existing within the stream.
     *
     * @return    void
     * @throws    DStreamWriteException    If the data could not be erased.
     */
    public function erase()
    {
        $handle = $this->getWriteHandle();
        if (ftruncate($handle, 0) === false) {
            throw new DStreamWriteException($this, 'Unable to erase file content.');
        }
    }

    /**
     * Returns the name of the file this steam relates to.
     *
     * @return    string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Returns the number of bytes contained in the stream.
     *
     * @return    int        The length of the stream in bytes.
     */
    public function getLength()
    {
        return filesize($this->filename);
    }

    /**
     * Returns the current pointer position.
     *
     * @return    int
     * @throws    DStreamSeekException    If the pointer position could
     *                                    not be retrieved.
     */
    public function getPosition()
    {
        if (is_resource($this->readHandle)) {
            $position = ftell($this->readHandle);
        } else {
            if (is_resource($this->writeHandle)) {
                $position = ftell($this->writeHandle);
            } else {
                $position = 0;
            }
        }

        return $position;
    }

    /**
     * Returns a handle that can be used to read from the file.
     *
     * @return    resource
     * @throws    DStreamReadException    If the file is not readable.
     */
    protected function getReadHandle()
    {
        if ($this->readHandle === null) {
            $this->testFileReadability($this->filename);
            $this->readHandle = fopen($this->filename, 'rb');
        }
        // Check for any unexpected error.
        if ($this->readHandle === false) {
            throw new DStreamReadException($this, 'Unable to open file for reading.');
        }

        return $this->readHandle;
    }

    /**
     * Returns a handle that can be used to write to the file.
     *
     * @return    resource
     * @throws    DStreamWriteException    If the file is not writable.
     */
    protected function getWriteHandle()
    {
        if ($this->writeHandle === null) {
            $this->testFileWritability($this->filename);
            $this->writeHandle = fopen($this->filename, 'ab');
        }
        // Check for any unexpected error.
        if ($this->writeHandle === false) {
            throw new DStreamWriteException($this, 'Unable to open file for writing.');
        }

        return $this->writeHandle;
    }

    /**
     * Reads data from the stream.
     *
     * @warning
     * It is possible that the script's memory limit may be exceeded if this
     * method is called on a stream with content exceeding the available memory.
     *
     * @param    int $length      The number of bytes of data to read,
     *                            or <code>null</code> to read all available data.
     *
     * @return    string    The data, or <code>null</code> if the end of
     *                    the stream has been reached.
     * @throws    DStreamReadException    If the data could not be read.
     */
    public function read($length = null)
    {
        // Retrieve a handle for reading.
        $handle = $this->getReadHandle();
        // Handle EOF, as fread returns an empty sttring.
        if (feof($handle)) {
            $content = null;
            // Return a specific length.
        } else {
            if ($length !== null) {
                $content = fread($handle, $length);
                // Or return entire content of the stream.
            } else {
                $content = '';
                do {
                    $content .= fread($handle, 8192);
                } while (!feof($handle));
            }
        }

        return $content;
    }

    /**
     * Reads a line of CSV data from the stream.
     *
     * @note
     * Blank lines within the stream will be skipped.
     *
     * @param    string $delimiter The field delimiter used by the CSV file.
     * @param    string $enclosure The field enclosure used by the CSV file.
     * @param    string $escape    The escape character used by the CSV file.
     *
     * @return    array    The CSV data as an array, or <code>null</code>
     *                    if the end of the stream has been reached.
     * @throws    DStreamReadException    If the data could not be read.
     */
    public function readCsvLine($delimiter = ',',
                                $enclosure = '"', $escape = '\\')
    {
        // Retrieve a handle for reading.
        $handle = $this->getReadHandle();
        // Attempt to parse a line of content.
        $line = fgetcsv($handle, null, $delimiter, $enclosure, $escape);
        // Convert error or EOF to correct return value or exception.
        if ($line === false) {
            if (feof($handle)) {
                $csvLine = null;
                // Don't know what this case is or how to trigger it.
                // PHP documentation references false returned on "other errors"?
                // See http://php.net/fgetcsv
            } else {
                throw new DStreamReadException($this, 'Invalid CSV data detected.');
            }
            // If a blank line was read, progress to the next line.
        } else {
            if ($line === array(null)) {
                $csvLine = $this->readCsvLine($delimiter, $enclosure, $escape);
            } else {
                $csvLine =& $line;
            }
        }

        return $csvLine;
    }

    /**
     * Reads a line of data from the stream.
     *
     * @note
     * The line ending will also be returned.
     *
     * @return    string    The data, or <code>null</code> if the end of
     *                    the stream has been reached.
     * @throws    DStreamReadException    If the data could not be read.
     */
    public function readLine()
    {
        // Retrieve a handle for reading.
        $handle = $this->getReadHandle();
        // Read a line.
        $firstLine = (ftell($handle) === 0);
        $rawLine = fgets($handle);
        // Return next line or EOF.
        if ($rawLine === false) {
            $line = null;
            // If this is the first line, remove the BOM if present.
        } else {
            if ($firstLine) {
                $line = DFileStream::removeBom($rawLine);
            } else {
                $line = $rawLine;
            }
        }

        return $line;
    }

    /**
     * Removes the BOM (byte order mark) from the provided string, if present.
     *
     * Currently supported encodings are:
     * - UTF-8
     * - UTF-16 (BE)
     * - UTF-16 (LE)
     * - UTF-32 (BE)
     * - UTF-32 (LE)
     *
     * @note
     * See http://en.wikipedia.org/wiki/Byte_order_mark for further information
     * about the byte order mark.
     *
     * @param    string $line The line to remove the BOM from.
     *
     * @return    string
     */
    public static function removeBom($line)
    {
        // Remove BOM if present.
        return preg_replace(DFileStream::REGEX_BOM, '', $line);
    }

    /**
     * Moves the pointer to the start of the stream.
     *
     * @return    void
     * @throws    DStreamSeekException    If the pointer could not be moved.
     */
    public function rewind()
    {
        if (is_resource($this->readHandle)) {
            rewind($this->readHandle);
        }
        if (is_resource($this->writeHandle)) {
            rewind($this->writeHandle);
        }
    }

    /**
     * Moves the pointer within the stream to a specified position.
     *
     * @param    int $position New pointer position.
     *
     * @return    void
     * @throws    DStreamSeekException    If the pointer could not be moved.
     */
    public function seek($position)
    {
        if (is_resource($this->readHandle)) {
            fseek($this->readHandle, $position);
        }
        if (is_resource($this->writeHandle)) {
            fseek($this->writeHandle, $position);
        }
    }

    /**
     * Tests a file to check if it will be readable.
     *
     * @param    string $filename Name of the file to test.
     *
     * @return    void
     * @throws    DStreamReadException    If the file is not readable.
     */
    protected function testFileReadability($filename)
    {
        if (!file_exists($filename)) {
            throw new DStreamReadException($this, 'File does not exist.');
        }
        if (!is_readable($filename)) {
            throw new DStreamReadException($this, 'File is not readable.');
        }
    }

    /**
     * Tests a file to check if it will be writable.
     *
     * @param    string $filename Name of the file to test.
     *
     * @return    void
     * @throws    DStreamWriteException    If the file is not writable.
     */
    protected function testFileWritability($filename)
    {
        // If the file doesn't exist, make sure we can write
        // to the directory in which the file will be created.
        while (!file_exists($filename)) {
            $filename = dirname($filename);
        }
        if (!is_writable($filename)) {
            throw new DStreamWriteException($this, 'File is not writable.');
        }
    }

    /**
     * Writes data to the stream.
     *
     * @warning
     * If there is already content in the file, this data will be appended
     * to the end. Call {@link DFileStream::erase()} to clear any existing
     * content before writing.
     *
     * @param    string $data Data to write to the stream.
     *
     * @return    void
     * @throws    DStreamWriteException    If the data could not be written.
     */
    public function write($data)
    {
        // Retrieve a handle for writing.
        $handle = $this->getWriteHandle();
        if (fwrite($handle, $data) === false) {
            throw new DStreamWriteException($this, 'Unable to write content to the file.');
        }
    }
}
