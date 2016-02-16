<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\request;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\utility\DList;

/**
 * List of {@link DFileUpload} objects.
 *
 * @author    Timothy de Paris
 */
class DFileUploads extends DList
{
    /**
     * Creates a new {@link DFileUploads} list by processing the provided file upload information.
     *
     * @param    array $uploads
     *
     * @throws    static
     */
    public function __construct(array $uploads = array())
    {
        $processed = array();
        foreach ($uploads as $key => $fileInfo) {
            if (!empty($fileInfo[ DFileUpload::FILES_NAME ])) {
                $this->processFileInfo($key, $fileInfo, $processed);
            }
        }
        parent::__construct($processed, true);
    }

    /**
     * Processes an entry from the file information array.
     *
     * @param    string $key
     * @param    array  $fileInfo
     * @param    array  $processed
     *
     * @throws    DInvalidParameterValueException
     * @return    void
     */
    protected function processFileInfo($key, array &$fileInfo, array &$processed)
    {
        // Handle grouped file uploads.
        if (is_array($fileInfo[ DFileUpload::FILES_TEMP_NAME ])) {
            $processed[ $key ] = array();
            $this->convertFilesArray($fileInfo, $processed[ $key ]);
            // Handle individual file uploads.
        } else {
            if (is_array($fileInfo)) {
                $processed[ $key ] = new DFileUpload($fileInfo);
            } else {
                throw new DInvalidParameterValueException(
                    'uploads',
                    array(get_called_class(), __FUNCTION__),
                    'Array of DFileUpload objects or arrays representing uploaded files.'
                );
            }
        }
    }

    /**
     * Converts an array of file uploads.
     *
     * @param    array $fileInfo      The uploaded file information.
     * @param    array $files         Pointer to the array to which converted
     *                                file upload information will be added.
     *
     * @return    void
     */
    protected function convertFilesArray(array $fileInfo, array &$files)
    {
        for ($i = 0; $i < count($fileInfo[ DFileUpload::FILES_TEMP_NAME ]); $i++) {
            // Ignore upload inputs with no file present.
            if ($fileInfo[ DFileUpload::FILES_NAME ][ $i ] === '') {
                continue;
            }
            // Create DFileUpload object.
            $files[] = new DFileUpload(array(
                                           DFileUpload::FILES_TEMP_NAME => $fileInfo[ DFileUpload::FILES_TEMP_NAME ][ $i ],
                                           DFileUpload::FILES_NAME      => $fileInfo[ DFileUpload::FILES_NAME ][ $i ],
                                           DFileUpload::FILES_ERROR     => $fileInfo[ DFileUpload::FILES_ERROR ][ $i ],
                                           DFileUpload::FILES_TYPE      => $fileInfo[ DFileUpload::FILES_TYPE ][ $i ],
                                           DFileUpload::FILES_SIZE      => $fileInfo[ DFileUpload::FILES_SIZE ][ $i ],
                                       ));
        }
    }
}
