<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\utility;

use app\decibel\debug\DErrorHandler;
use app\decibel\utility\DInvalidPageSizeException;
use app\decibel\utility\DPage;
use app\decibel\utility\DUtilityData;
use Countable;

/**
 * Generated and provides information about Dpagination for a set of data.
 *
 * The object contains functions to determine, retrieve
 * and display Dpagination information.
 *
 * The contents of the page can also be stored using
 * the {@link app::decibel::utility::DPagination::$pageContent DPagination::$pageContent} property.
 *
 * @author        Timothy de Paris
 */
class DPagination extends DUtilityData implements Countable
{
    /**
     * The format of the first page URL ending.
     *
     * @var        string
     */
    const URL_ENDING_TYPE_1 = 'page1';
    /**
     * The format of the page URL endings.
     *
     * @var        string
     */
    const URL_ENDING_TYPE_N = 'pageN';
    /**
     * The first page.
     *
     * @var        string
     */
    const PAGE_FIRST = 'first';
    /**
     * The previous page.
     *
     * @var        string
     */
    const PAGE_PREVIOUS = 'previous';
    /**
     * The page.
     *
     * @var        string
     */
    const PAGE_NUMBER = 'page';
    /**
     * Contant for the next page.
     *
     * @var        string
     */
    const PAGE_NEXT = 'next';
    /**
     * Contant for the last page.
     *
     * @var        string
     */
    const PAGE_LAST = 'last';
    /**
     * Placeholder used for page numbers within pagination templates.
     *
     * @var        string
     */
    const PLACEHOLDER_PAGE = '#page#';
    /**
     * The total number of results being paged.
     *
     * @var        int
     */
    protected $totalResults;
    /**
     * The current page being viewed.
     *
     * @var        int
     */
    protected $pageNumber;
    /**
     * The number of results included in each page.
     *
     * @var        int
     */
    protected $pageSize;
    /**
     * The total number of pages available.
     *
     * @var        int
     */
    protected $totalPages;
    /**
     * The maximum number of page links that will be displayed.
     *
     * @var        int
     */
    protected $showPages;
    /**
     * DPage objects representing the rendered paging.
     *
     * @var        array
     */
    protected $pages;
    /**
     * The page content.
     *
     * @var        array
     */
    protected $pageContent;
    /**
     * DPage object representing the previous page.
     *
     * @var        DPage
     */
    protected $previousPage;
    /**
     * DPage object representing the next page.
     *
     * @var        DPage
     */
    protected $nextPage;
    /**
     * Labels used for First, Previous, Next and Last options when
     * displaying paging.
     *
     * @var        array
     */
    protected $labels = array(
        self::PAGE_FIRST    => '&lt;&lt;',
        self::PAGE_PREVIOUS => '&lt;',
        self::PAGE_NUMBER   => self::PLACEHOLDER_PAGE,
        self::PAGE_NEXT     => '&gt;',
        self::PAGE_LAST     => '&gt;&gt;',
    );
    /**
     * Titles used for First, Previous, Next and Last options when
     * displaying paging.
     *
     * These are displayed in the title attribute of the HTML anchor tag.
     *
     * @var        array
     */
    protected $titles = array(
        self::PAGE_FIRST    => 'First Page',
        self::PAGE_PREVIOUS => 'Previous Page',
        self::PAGE_NUMBER   => 'Page #page#',
        self::PAGE_NEXT     => 'Next Page',
        self::PAGE_LAST     => 'Last Page',
    );
    /**
     * The format for URL endings of pages.
     *
     * The first page is described differently to subsequent pages.
     *
     * @var        array
     */
    protected $urlEndings = array(
        self::URL_ENDING_TYPE_1 => '',
        self::URL_ENDING_TYPE_N => 'Page-#page#',
    );
    /**
     * Parameters to be appended to the end of each page URL.
     *
     * @var        string
     */
    protected $urlParameters = '';

    /**
     * Creates a new DPagination object, determining paging information for the
     * specified details.
     *
     * @param    int $totalResults        Total number of results being paged.
     * @param    int $pageNumber          Current page being viewed.
     * @param    int $pageSize            Number of results included in each page.
     * @param    int $showPages           Maximum number of page links that
     *                                    will be displayed.
     *
     * @return    static
     * @throws    DInvalidPageSizeException    If an invalid value is provided
     *                                        for the $pageNumber parameter.
     */
    public function __construct($totalResults, $pageNumber,
                                $pageSize, $showPages = 5)
    {
        parent::__construct();
        // Validate parameters.
        if (!is_numeric($pageSize)
            || $pageSize == 0
        ) {
            $exception = new DInvalidPageSizeException((string)$pageSize);
            DErrorHandler::throwException($exception);
            $pageSize = 10;
        }
        // Store provided information.
        $this->totalResults = (int)$totalResults;
        $this->pageNumber = (int)$pageNumber;
        $this->pageSize = (int)$pageSize;
        $this->showPages = (int)$showPages;
        $this->totalPages = (int)ceil($this->totalResults / $this->pageSize);
    }

    /**
     * Calculates paging based on the provided information.
     *
     * This function must be called in order to calculate page information
     * before being displayed.
     *
     * @param    bool $includeAll     If true, all page controls will be included.
     *                                If false, only the applicable page controls
     *                                will be included (for example, the First and
     *                                Previous controls would be omitted if the
     *                                current page is the first page).
     *
     * @return    void
     */
    public function calculatePaging($includeAll = false)
    {
        if ($this->canCalculatePaging($includeAll)) {
            // First and Previous pages.
            if ($includeAll || $this->pageNumber > 1) {
                $this->calculateFirstPage($includeAll);
                $this->calculatePreviousPage($includeAll);
            }
            // Determine which pages to show.
            if ($this->totalPages <= $this->showPages) {
                $start = 0;
                $end = $this->totalPages;
            } else {
                $start = (int)ceil($this->pageNumber - ($this->showPages / 2));
                $end = (int)floor($this->pageNumber + ($this->showPages / 2));
            }
            // Add pages.
            for ($page = $start; $page <= $end; ++$page) {
                $this->calculatePage($page);
            }
            // First and Previous pages.
            if ($includeAll || $this->pageNumber < $this->totalPages) {
                $this->calculateNextPage($includeAll);
                $this->calculateLastPage($includeAll);
            }
        }
    }

    /**
     * Calculates the 'first' page control.
     *
     * @param    bool $includeAll If true, all page controls will be included.
     *
     * @return    void
     */
    protected function calculateFirstPage($includeAll = false)
    {
        if ($this->labels[ self::PAGE_FIRST ]
            && ($includeAll || $this->totalPages > $this->showPages)
        ) {
            $this->pages[] = new DPage(
                self::PAGE_FIRST,
                1,
                false,
                $this->getUrlEnding(1),
                $this->labels[ self::PAGE_FIRST ],
                $this->titles[ self::PAGE_FIRST ],
                $includeAll && $this->pageNumber === 1
            );
        }
    }

    /**
     * Calculates the 'previous' page control.
     *
     * @param    bool $includeAll If true, all page controls will be included.
     *
     * @return    void
     */
    protected function calculatePreviousPage($includeAll = false)
    {
        if ($this->labels[ self::PAGE_PREVIOUS ]) {
            $page = $this->pageNumber - 1;
            $this->previousPage = new DPage(
                self::PAGE_PREVIOUS,
                $page,
                false,
                $this->getUrlEnding($page),
                $this->labels[ self::PAGE_PREVIOUS ],
                $this->titles[ self::PAGE_PREVIOUS ],
                $includeAll && $this->pageNumber === 1
            );
            $this->pages[] = $this->previousPage;
        }
    }

    /**
     *
     * @param    int $page
     *
     * @return    void
     */
    protected function calculatePage($page)
    {
        if ($page > 0
            && $page <= $this->totalPages
        ) {
            $label = str_replace(self::PLACEHOLDER_PAGE, $page, $this->labels[ self::PAGE_NUMBER ]);
            $title = str_replace(self::PLACEHOLDER_PAGE, $page, $this->titles[ self::PAGE_NUMBER ]);
            $pageObject = new DPage(
                self::PAGE_NUMBER,
                $page,
                $this->pageNumber === $page,
                $this->getUrlEnding($page),
                $label,
                $title
            );
            $this->pages[] = $pageObject;
        }
    }

    /**
     * Calculates the 'next' page control.
     *
     * @param    bool $includeAll If true, all page controls will be included.
     *
     * @return    void
     */
    protected function calculateNextPage($includeAll = false)
    {
        if ($this->labels[ self::PAGE_NEXT ]) {
            $page = $this->pageNumber + 1;
            $this->nextPage = new DPage(
                self::PAGE_NEXT,
                $page,
                false,
                $this->getUrlEnding($page),
                $this->labels[ self::PAGE_NEXT ],
                $this->titles[ self::PAGE_NEXT ],
                $includeAll && $this->pageNumber >= $this->totalPages
            );
            $this->pages[] = $this->nextPage;
        }
    }

    /**
     * Calculates the 'last' page control.
     *
     * @param    bool $includeAll If true, all page controls will be included.
     *
     * @return    void
     */
    protected function calculateLastPage($includeAll = false)
    {
        if ($this->labels[ self::PAGE_LAST ]
            && ($includeAll || $this->totalPages > $this->showPages)
        ) {
            $page = $this->totalPages;
            $this->pages[] = new DPage(
                self::PAGE_LAST,
                $page,
                false,
                $this->getUrlEnding($page),
                $this->labels[ self::PAGE_LAST ],
                $this->titles[ self::PAGE_LAST ],
                $includeAll && $this->pageNumber >= $this->totalPages
            );
        }
    }

    /**
     * Determines if paging can be calculated.
     *
     * @param    bool $includeAll
     *
     * @return    bool
     */
    protected function canCalculatePaging($includeAll = false)
    {
        // Only do this once...
        return $this->pages === null
        // ...and only if neccessary.
        && ($includeAll || $this->totalPages > 1);
    }

    /**
     * Returns the number of items of page content.
     *
     * Implemented for the Countable interface.
     *
     * @return    int
     */
    public function count()
    {
        return count($this->pageContent);
    }

    /**
     * Defines fields available for this object.
     *
     * @return    void
     */
    protected function define()
    {
    }

    /**
     * Returns the URL ending for the specified page number.
     *
     * @param    int $page The page number.
     *
     * @return    string
     */
    protected function getUrlEnding($page)
    {
        if ($page === 1) {
            $format = $this->urlEndings[ self::URL_ENDING_TYPE_1 ];
        } else {
            $format = $this->urlEndings[ self::URL_ENDING_TYPE_N ];
        }
        $urlEnding = str_replace(self::PLACEHOLDER_PAGE, $page, $format);

        // Add URL parameters and return.
        return ($urlEnding . $this->urlParameters);
    }

    /**
     * Set the page label.
     *
     * @param    string $type  Name of the page (first, next, previous or last).
     * @param    string $label The label name, or false if this type of page shouldn't be shown.
     *
     * @return    void
     */
    public function setPageLabel($type, $label)
    {
        $this->labels[ $type ] = $label;
    }

    /**
     * Sets the format for a type of URL ending.
     *
     * <code>@#page@#</code> should be used to specify the location of the page number.
     *
     * @note
     * The URL ending format should not include any query parameters, these can be specified
     * using the {@link DPagination::setUrlParameters()} method.
     *
     * @param    string $type     The URL ending type. Can be one of:
     *                            {@link app::decibel::utility::DPagination::URL_ENDING_TYPE_1
     *                            DPagination::URL_ENDING_TYPE_1}
     *                            {@link app::decibel::utility::DPagination::URL_ENDING_TYPE_N
     *                            DPagination::URL_ENDING_TYPE_N}
     * @param    string $format   The URL ending format. A <code>@#page@#</code> placeholder should
     *                            be used to denote the location of the page number.
     *
     * @return    void
     */
    public function setUrlEndingFormat($type, $format)
    {
        // Normalise the trailing slash, as it may or may not be provided.
        $this->urlEndings[ $type ] = rtrim($format, '/') . '/';
    }

    /**
     * Sets URL parameters to be appended to the end of each page URL.
     *
     * @param    string $urlParameters The URL parameters.
     *
     * @return    void
     */
    public function setUrlParameters($urlParameters)
    {
        // Normalise the question mark, as it may or may not be provided.
        $this->urlParameters = '?' . ltrim($urlParameters, '?');
    }
}
