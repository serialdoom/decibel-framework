<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\request;

use app\decibel\file\DFile;
use app\decibel\file\DLocalFileSystem;
use app\decibel\regional\DLabel;

/**
 * Wrapper class for file upload information.
 *
 * This class normalises information from the <code>$_FILES</code> array,
 * allowing access via the {@link app::decibel::http::request::DRequest DRequest} object.
 *
 * @author        Timothy de Paris
 */
class DFileUpload
{
    /**
     * Denotes an upload error due to the file size exceeding the allowable
     * upload size under the current PHP configuration.
     *
     * @var        int
     */
    const ERROR_SIZE = 1;

    /**
     * Denotes an upload error due to an issue occurring during file upload,
     * usually due to a connection failure.
     *
     * @var        int
     */
    const ERROR_CONNECTION = 2;

    /**
     * Denotes an upload error due to an issue occurring on the server,
     * usually due to incorrect configuration of the server.
     *
     * @var        int
     */
    const ERROR_SERVER = 3;

    /**
     * Denotes an error due to an attempt by a user to perform a malicious
     * action.
     *
     * @var        int
     */
    const ERROR_MALICIOUS = 4;

    /**
     * 'error' key for PHP $_FILES array.
     *
     * @var        string
     */
    const FILES_ERROR = 'error';

    /**
     * 'name' key for PHP $_FILES array.
     *
     * @var        string
     */
    const FILES_NAME = 'name';

    /**
     * 'size' key for PHP $_FILES array.
     *
     * @var        string
     */
    const FILES_SIZE = 'size';

    /**
     * 'tmp_name' key for PHP $_FILES array.
     *
     * @var        string
     */
    const FILES_TEMP_NAME = 'tmp_name';

    /**
     * 'type' key for PHP $_FILES array.
     *
     * @var        string
     */
    const FILES_TYPE = 'type';

    /**
     * Contains the error code, if an error occurred during file upload.
     *
     * This will be one of {@link app::decibel::http::request::DFileUpload::ERROR_SIZE DFileUpload::ERROR_SIZE},
     * {@link app::decibel::http::request::DFileUpload::ERROR_CONNECTION DFileUpload::ERROR_CONNECTION}
     * or {@link app::decibel::http::request::DFileUpload::ERROR_SERVER DFileUpload::ERROR_SERVER}.
     *
     * @var        int
     */
    protected $error;

    /**
     * The original name of the uploaded file.
     *
     * @var        string
     */
    protected $filename;

    /**
     * The mime type of the uploaded file, as provided by the uploading browser.
     *
     * @var        string
     */
    protected $mimeType;

    /**
     * Size of the uploaded file, in bytes.
     *
     * @var        int
     */
    protected $size;

    /**
     * The temporary location of the uploaded file on the server.
     *
     * @var        string
     */
    protected $tmpLocation;

    /**
     * The extension of the uploaded file.
     *
     * @var        string
     */
    protected $extension;

    /**
     * List of labels used to describe upload errors.
     *
     * @var        string
     */
    private static $errorMessageLabels = array(
        self::ERROR_CONNECTION => 'errorConnection',
        self::ERROR_MALICIOUS  => 'errorMalicious',
        self::ERROR_SERVER     => 'errorServer',
        self::ERROR_SIZE       => 'errorSize',
    );

    /**
     * Creates a new file upload wrapper object.
     *
     * @param    array $fileInfo      Associative array from <code>$_FILES</code>.
     * @param    bool  $testMalicious Whether to test for malicious uploads.
     *
     * @return    static
     */
    public function __construct(array $fileInfo, $testMalicious = true)
    {
        $name = str_replace('\\', '/', $fileInfo[ self::FILES_NAME ]);
        $tmpName = str_replace('\\', '/', $fileInfo[ self::FILES_TEMP_NAME ]);
        $this->filename = $name;
        $this->tmpLocation = $tmpName;
        $this->extension = preg_replace('/^.*\.([^\.]+)$/', '$1', $name);
        $this->mimeType = $fileInfo[ self::FILES_TYPE ];
        $this->size = $fileInfo[ self::FILES_SIZE ];
        // Check this file actually was uploaded.
        if ($testMalicious
            && $fileInfo[ self::FILES_ERROR ] === UPLOAD_ERR_OK
            && !is_uploaded_file($fileInfo[ self::FILES_TEMP_NAME ])
        ) {
            $this->error = self::ERROR_MALICIOUS;
        } else {
            switch ($fileInfo[ self::FILES_ERROR ]) {
                case UPLOAD_ERR_OK:
                    $this->error = null;
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $this->error = self::ERROR_SIZE;
                    break;
                case UPLOAD_ERR_PARTIAL:
                case UPLOAD_ERR_NO_FILE:
                    $this->error = self::ERROR_CONNECTION;
                    break;
                default:
                    $this->error = self::ERROR_SERVER;
                    break;
            }
        }
    }

    /**
     * Copies the uploaded file.
     *
     * @param    string $folder       The folder to copy the file to. If it doesn't
     *                                exist, the folder will be created.
     * @param    string $filename     The name of the copied file.
     *                                If not provided, the original filename
     *                                will be used.
     *
     * @return    bool
     */
    public function copyTo($folder, $filename = null)
    {
        if ($filename === null) {
            $filename = $this->filename;
        }
        // Create the folder if it doesn't exist.
        if (!file_exists($folder)) {
            $fileSystem = new DLocalFileSystem();
            $fileSystem->mkdir($folder);
        }

        return copy($this->tmpLocation, "{$folder}{$filename}");
    }

    /**
     * Returns the error code for this file upload.
     *
     * @return    int        One of:
     *                    - {@link DFileUpload::ERROR_SIZE}
     *                    - {@link DFileUpload::ERROR_CONNECTION}
     *                    - {@link DFileUpload::ERROR_SERVER}
     *                    - {@link DFileUpload::ERROR_MALICIOUS}
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns a human-readable error message for this file upload.
     *
     * @return    string    The error message, or null if no error occured.
     */
    public function getErrorMessage()
    {
        if (array_key_exists($this->error, self::$errorMessageLabels)) {
            $size = DFile::bytesToString(DFile::getMaxUploadSize(), 0);
            $vars = array('size' => $size);
            $message = new DLabel(
                self::class,
                self::$errorMessageLabels[ $this->error ],
                $vars
            );
        } else {
            $message = null;
        }

        return $message;
    }

    /**
     * Returns a list of available error types for file uploads.
     *
     * @return    array
     */
    public static function getErrorTypes()
    {
        return array(
            self::ERROR_SIZE,
            self::ERROR_CONNECTION,
            self::ERROR_SERVER,
            self::ERROR_MALICIOUS,
        );
    }

    /**
     * Returns the extension of the uploaded file.
     *
     * @return    string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Returns the name of the uploaded file.
     *
     * @return    string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Returns the mime type of the uploaded file.
     *
     * @return    string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Returns the size of the uploaded file in bytes.
     *
     * @note
     * See {@link DFile::bytesToString()} for converting this value to a string.
     *
     * @return    int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Returns the temporary location of the uploaded file on the server.
     *
     * @note
     * The {@link DFileUpload::copyTo()} method should be used to relocate
     * the uploaded file if required.
     *
     * @return    string
     */
    public function getTemporaryLocation()
    {
        return $this->tmpLocation;
    }

    /**
     * Determines if the upload was successful.
     *
     * @return    bool
     */
    public function uploadSuccessful()
    {
        return !$this->error;
    }
}
