<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\request;

/**
 * An HTTP request method that contains an entity-body.
 *
 * @section   versioning Version Control
 *
 * @author    Timothy de Paris
 */
abstract class DEntityBodyRequest extends DRequest
{
    /**
     * Returns the body of this request, if any.
     *
     * @code
     * use app\decibel\http\request\DRequest;
     * use app\decibel\http\request\DPostRequest;
     *
     * $request = DRequest::load();
     *
     * if ($request instanceof DPostRequest) {
     *    debug($request->getBody());
     * }
     * @endcode
     *
     * @return    string
     */
    public function getBody()
    {
        return $this->information->getBody();
    }

    /**
     * Returns any files uploaded with the request.
     *
     * @return    DFileUploads
     */
    public function getUploadedFiles()
    {
        return $this->information->getUploadedFiles();
    }
}
