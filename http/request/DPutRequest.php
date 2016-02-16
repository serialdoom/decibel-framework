<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\request;

/**
 * Request wrapper for HTTP PUT method.
 *
 * @section   versioning Version Control
 *
 * @author    Timothy de Paris
 */
class DPutRequest extends DEntityBodyRequest
{
    /**
     * 'PUT' HTTP method.
     *
     * @var        string
     */
    const METHOD = 'PUT';
}
