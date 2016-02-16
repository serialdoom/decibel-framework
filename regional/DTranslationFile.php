<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional;

use app\decibel\stream\DReadableStream;
use app\decibel\stream\DStreamReadException;

/**
 * Provides a wrapper for accessing translation files.
 *
 * @author    Timothy de Paris
 */
class DTranslationFile extends DTranslationSource
{
    /**
     * Regular expression for matching a label from a line
     * of a translation file.
     *
     * @var        string
     */
    const REGEX_LABEL = '/^\s*([^-=\s]+)-([^-=\s]+)\s*=\s*([^\n\r]+)\s*$/m';

    /**
     * Stream from which translations will be read.
     *
     * @var        DReadableStream
     */
    protected $stream;

    /**
     * Creates a {@link DTranslationFile} taking content from the provided stream.
     *
     * @param    DReadableStream $stream       Stream from which translations will be read.
     * @param    string          $languageCode Language for which this file provides translations.
     *
     * @return    static
     */
    public function __construct(DReadableStream $stream, $languageCode)
    {
        parent::__construct($languageCode);
        $this->stream = $stream;
    }

    /**
     * Parses the translation file and returns a list of defined labels.
     *
     * @return    array    List of labels with namespaced label names as keys.
     * @throws    DStreamReadException    If labels are unable to be read from the stream.
     */
    public function getLabels()
    {
        $labels = array();
        while (($line = $this->stream->readLine()) !== null) {
            $this->getLabel($line, $labels);
        }

        return $labels;
    }

    /**
     * Parses a line of translation file content to extract a label.
     *
     * @note
     * This method will override existing labels with the same namespace and name.
     *
     * @param    string $line   Line of translation file content.
     * @param    array  $labels Pointer in which valid labels will be returned.
     *
     * @return    bool    Whether a label was matched.
     */
    protected function getLabel($line, array &$labels)
    {
        $matches = null;
        if (preg_match(self::REGEX_LABEL, $line, $matches)) {
            $labels[ $matches[1] ][ $matches[2] ] = $matches[3];
            $labelMatched = true;
        } else {
            $labelMatched = false;
        }

        return $labelMatched;
    }
}
