<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\utility\DUtilityData;

/**
 * Contains information about a page for a set of data.
 *
 * @author        Timothy de Paris
 */
class DPage extends DUtilityData
{
    /**
     * The type of page.
     *
     * @var        string
     */
    protected $type;
    /**
     * The number of this page.
     *
     * @var        int
     */
    protected $pageNumber;
    /**
     * Whether the page is currently being shown.
     *
     * @var        bool
     */
    protected $selected;
    /**
     *The URL ending for the page.
     *
     * @var        string
     */
    protected $urlEnding;
    /**
     * The label for the page.
     *
     * @var        string
     */
    protected $label;
    /**
     * The title of the page.
     *
     * @var        string
     */
    protected $title;
    /**
     * Whether the page control is enabled.
     *
     * @var        string
     */
    protected $disabled;

    /**
     * Creates a Page record for a Pagination object.
     *
     * @param   string  $type       The page type. Must be one of:
     *                              - {@link DPagination::PAGE_FIRST}
     *                              - {@link DPagination::PAGE_PREVIOUS}
     *                              - {@link DPagination::PAGE_NUMBER}
     *                              - {@link DPagination::PAGE_NEXT}
     *                              - {@link DPagination::PAGE_LAST}
     * @param    int    $pageNumber The number of the page.
     * @param    bool   $selected   Whether the page is currently being shown.
     * @param    string $urlEnding  The URL ending for the page.
     * @param    string $label      The label for the page.
     * @param    string $title      The title of the page.
     * @param    bool   $disabled   Whether the page control is clickable.
     *
     * @return    static
     */
    public function __construct($type, $pageNumber, $selected, $urlEnding,
                                $label, $title, $disabled = false)
    {
        parent::__construct();
        $this->type = $type;
        $this->pageNumber = $pageNumber;
        $this->selected = $selected;
        $this->urlEnding = $urlEnding;
        $this->label = $label;
        $this->title = $title;
        $this->disabled = $disabled;
    }

    /**
     * Defines fields available for this object.
     *
     * @return    void
     */
    protected function define()
    {
    }
}
