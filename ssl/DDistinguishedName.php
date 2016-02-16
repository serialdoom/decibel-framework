<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

/**
 * Wrapper class for DN information for a SSL certificate.
 *
 * @author        Timothy de Paris
 */
class DDistinguishedName
{
    /**
     * Two letter country code, for example "GB".
     *
     * @var        string
     */
    protected $countryName;
    /**
     * State or province, for example "London".
     *
     * @var        string
     */
    protected $stateOrProvinceName;
    /**
     * Locality, for example "City of London".
     *
     * @var        string
     */
    protected $localityName;
    /**
     * Organisation, for example "Decibel Technology".
     *
     * @var        string
     */
    protected $organizationName;
    /**
     * Organisational unit, for example "Product Development".
     *
     * @var        string
     */
    protected $organizationalUnitName;
    /**
     * Common name, for example "www.decibeltechnology.com".
     *
     * @var        string
     */
    protected $commonName;
    /**
     * Email address, for example "team@decibeltechnology.com".
     *
     * @var        string
     */
    protected $emailAddress;
    /**
     * Subject alternative names for this DN.
     *
     * @var        array
     */
    private $sans = array();

    /**
     * Creates a new distinguished name.
     *
     * @param    string $countryName            Two letter country code, for example "UK".
     * @param    string $stateOrProvinceName    State or province, for example "London".
     * @param    string $localityName           Locality, for example "City of London".
     * @param    string $organizationName       Organisation, for example "Decibel Technology".
     * @param    string $organizationalUnitName Organisational unit, for example "Product Development".
     * @param    string $commonName             Common name, for example "www.decibeltechnology.com".
     * @param    string $emailAddress           Email address, for example "team@decibeltechnology.com".
     *
     * @return    static
     */
    public function __construct($countryName, $stateOrProvinceName,
                                $localityName, $organizationName, $organizationalUnitName,
                                $commonName, $emailAddress = null)
    {
        $this->countryName = $countryName;
        $this->stateOrProvinceName = $stateOrProvinceName;
        $this->localityName = $localityName;
        $this->organizationName = $organizationName;
        $this->organizationalUnitName = $organizationalUnitName;
        $this->commonName = $commonName;
        $this->emailAddress = $emailAddress;
    }

    /**
     * Adds a subject alternative name to this distinguished name.
     *
     * @note
     * Multiple subject alternative names can be added by calling this
     * method multiple times.
     *
     * @param    string $name The subject alternative name.
     *
     * @return    static
     */
    public function addSubjectAltName($name)
    {
        $this->sans[] = $name;

        return $this;
    }

    /**
     * Creates a distinguised name from a subject array.
     *
     * @param    array $subject       Subject containing keys:
     *                                - <code>C</code>
     *                                - <code>ST</code>
     *                                - <code>L</code>
     *                                - <code>O</code>
     *                                - <code>OU</code>
     *                                - <code>CN</code>
     *
     * @return    static
     */
    public static function createFromSubject(array $subject)
    {
        set_default($subject['C'], '');
        set_default($subject['ST'], '');
        set_default($subject['L'], '');
        set_default($subject['O'], '');
        set_default($subject['OU'], '');
        set_default($subject['CN'], '');

        return new static(
            $subject['C'],
            $subject['ST'],
            $subject['L'],
            $subject['O'],
            $subject['OU'],
            $subject['CN']
        );
    }

    /**
     * Returns the distinguished name formatted as the subject of a certificate.
     *
     * @return    array    Subject containing keys:
     *                    - <code>C</code>
     *                    - <code>ST</code>
     *                    - <code>L</code>
     *                    - <code>O</code>
     *                    - <code>OU</code>
     *                    - <code>CN</code>
     */
    public function getSubject()
    {
        return array(
            'C'  => $this->countryName,
            'ST' => $this->stateOrProvinceName,
            'L'  => $this->localityName,
            'O'  => $this->organizationName,
            'OU' => $this->organizationalUnitName,
            'CN' => $this->commonName,
        );
    }

    /**
     * Returns subject alternative names for this distinguished name,
     * if any are available.
     *
     * @return    array
     */
    public function getSubjectAltNames()
    {
        return $this->sans;
    }
}
