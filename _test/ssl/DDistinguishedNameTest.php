<?php
namespace tests\app\decibel\ssl;

use app\decibel\ssl\DDistinguishedName;
use app\decibel\test\DTestCase;

/**
 * Test class for DDistinguishedName.
 */
class DDistinguishedNameTest extends DTestCase
{
    /**
     *
     */
    protected function setUp()
    {
        if (!extension_loaded('openssl')) {
            $this->markTestSkipped(
                'The OpenSSL extension is not available.'
            );
        }
    }

    /**
     * @covers app\decibel\ssl\DDistinguishedName::__construct
     */
    public function test__construct()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $dn = new DDistinguishedName(
            'countryName',
            'stateOrProvinceName',
            'localityName',
            'organizationName',
            'organizationalUnitName',
            'commonName',
            'emailAddress'
        );
        $this->assertSame('countryName', $dn->countryName);
        $this->assertSame('stateOrProvinceName', $dn->stateOrProvinceName);
        $this->assertSame('localityName', $dn->localityName);
        $this->assertSame('organizationName', $dn->organizationName);
        $this->assertSame('organizationalUnitName', $dn->organizationalUnitName);
        $this->assertSame('commonName', $dn->commonName);
        $this->assertSame('emailAddress', $dn->emailAddress);
    }

    /**
     * @covers app\decibel\ssl\DDistinguishedName::addSubjectAltName
     * @covers app\decibel\ssl\DDistinguishedName::getSubjectAltNames
     */
    public function testaddSubjectAltName()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $dn = new DDistinguishedName(
            'countryName',
            'stateOrProvinceName',
            'localityName',
            'organizationName',
            'organizationalUnitName',
            'commonName'
        );
        $this->assertSame(array(), $dn->getSubjectAltNames());
        $this->assertSame($dn, $dn->addSubjectAltName('name1'));
        $this->assertSame($dn, $dn->addSubjectAltName('name2'));
        $this->assertSame(
            array(
                'name1',
                'name2',
            ),
            $dn->getSubjectAltNames()
        );
    }

    /**
     * @covers app\decibel\ssl\DDistinguishedName::createFromSubject
     */
    public function testcreateFromSubject()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $dn = DDistinguishedName::createFromSubject(
            array(
                'C'  => 'countryName',
                'ST' => 'stateOrProvinceName',
                'L'  => 'localityName',
                'O'  => 'organizationName',
                'OU' => 'organizationalUnitName',
                'CN' => 'commonName',
            )
        );
        $this->assertSame(
            array(
                'C'  => 'countryName',
                'ST' => 'stateOrProvinceName',
                'L'  => 'localityName',
                'O'  => 'organizationName',
                'OU' => 'organizationalUnitName',
                'CN' => 'commonName',
            ),
            $dn->getSubject()
        );
    }

    /**
     * @covers app\decibel\ssl\DDistinguishedName::createFromSubject
     */
    public function testcreateFromSubject_missingKeys()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $dn = DDistinguishedName::createFromSubject(
            array(
                'CN' => 'commonName',
            )
        );
        $this->assertSame(
            array(
                'C'  => '',
                'ST' => '',
                'L'  => '',
                'O'  => '',
                'OU' => '',
                'CN' => 'commonName',
            ),
            $dn->getSubject()
        );
    }

    /**
     * @covers app\decibel\ssl\DDistinguishedName::getSubject
     */
    public function testgetSubject()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $dn = new DDistinguishedName(
            'countryName',
            'stateOrProvinceName',
            'localityName',
            'organizationName',
            'organizationalUnitName',
            'commonName',
            'emailAddress'
        );
        $this->assertSame(
            array(
                'C'  => 'countryName',
                'ST' => 'stateOrProvinceName',
                'L'  => 'localityName',
                'O'  => 'organizationName',
                'OU' => 'organizationalUnitName',
                'CN' => 'commonName',
            ),
            $dn->getSubject()
        );
    }
}
