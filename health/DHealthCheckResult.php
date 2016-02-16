<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\health;

/**
 * Contains the results of a health check performed by a {@link DHealthCheck}.
 *
 * @author        Timothy de Paris
 */
class DHealthCheckResult
{
    /**
     * Health check message.
     *
     * @var        int
     */
    const HEALTH_CHECK_MESSAGE = 1;
    /**
     * Health check warning.
     *
     * @var        int
     */
    const HEALTH_CHECK_WARNING = 2;
    /**
     * Health check error.
     *
     * @var        int
     */
    const HEALTH_CHECK_ERROR = 3;
    /**
     * Type of result.
     *
     * @var        string
     */
    protected $type;
    /**
     * Description of the result.
     *
     * @var        string
     */
    protected $description;
    /**
     * Unique ID for this health check result.
     *
     * This is a hash of the type and description, ignoring any variables
     * that will be substituted into the description.
     *
     * @var        string
     */
    protected $id;
    /**
     * A URL at which this issue can be corrected.
     *
     * @var        string
     */
    protected $repairLink;

    /**
     * Creates a new DHealthCheckResult object.
     *
     * @param    int    $type             The result type. One of:
     *                                    - {@link DHealthCheckResult::HEALTH_CHECK_MESSAGE}
     *                                    - {@link DHealthCheckResult::HEALTH_CHECK_WARNING}
     *                                    - {@link DHealthCheckResult::HEALTH_CHECK_ERROR}
     * @param    DLabel $description      Description of the result.
     * @param    array  $variables        Variables to be substituted into
     *                                    the result.
     * @param    string $repairLink       A URL at which this issue can be corrected.
     *
     * @return    static
     */
    public function __construct($type, $description,
                                array $variables = array(), $repairLink = null)
    {
        $this->id = md5($type . (string)$description);
        $this->type = $type;
        $this->description = vsprintf((string)$description, $variables);
        $this->repairLink = $repairLink;
    }

    /**
     * Returns a description of the health check result.
     *
     * @return    string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the unique ID of the health check result type.
     *
     * @note
     * The unique ID is calculated based on the <code>$type</code>
     * and <code>$description</code> parameters provided to the constructor.
     * This means that multiple individual health check results of the same
     * type may have the same ID, as they indicate the same type of issue.
     *
     * @return    string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns a URL at which this issue can be corrected.
     *
     * @return    string    The URL, or <code>null</code> if no URL is available.
     */
    public function getRepairLink()
    {
        return $this->repairLink;
    }

    /**
     * Returns the type of health check result.
     *
     * @return    int        One of:
     *                    - {@link DHealthCheckResult::HEALTH_CHECK_MESSAGE}
     *                    - {@link DHealthCheckResult::HEALTH_CHECK_WARNING}
     *                    - {@link DHealthCheckResult::HEALTH_CHECK_ERROR}
     */
    public function getType()
    {
        return $this->type;
    }
}
