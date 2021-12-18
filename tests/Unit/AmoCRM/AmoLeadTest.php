<?php

declare(strict_types=1);

namespace Tests\Unit\AmoCRM;

use PHPUnit\Framework\TestCase;
use AmoCRM\AmoLead;

class AmoLeadTest extends TestCase
{
    /**
     * @var AmoLead
     */
    private $amoLead;

    protected function setUp()
    {
        parent::setUp();
        $this->amoLead = new AmoLead();
    }

    // addContacts() tests

    public function testAddContactsInteger()
    {
        $this->amoLead->addContacts(12345678);
        $this->assertEquals([ 'id' => [ 12345678 ] ], $this->amoLead->contacts);
    }

    public function testAddContactsArray()
    {
        $this->amoLead->addContacts([ 12345678, 12345679, 123456780 ]);
        $this->assertEquals([ 'id' => [ 12345678, 12345679, 123456780 ] ], $this->amoLead->contacts);
    }

    public function testAddContactsIntegerDuplicate()
    {
        $this->amoLead->addContacts(12345678);
        $this->amoLead->addContacts(12345678);
        $this->amoLead->addContacts(12345678);
        $this->assertEquals([ 'id' => [ 12345678 ] ], $this->amoLead->contacts);
    }

    public function testAddContactsArrayDuplicate()
    {
        $this->amoLead->addContacts([ 12345678, 12345679, 123456780 ]);
        $this->amoLead->addContacts([ 123456780, 12345678, 12345679, 123456781 ]);
        $this->assertEquals([ 'id' => [ 12345678, 12345679, 123456780, 123456781 ] ], $this->amoLead->contacts);
    }

    // removeContacts() tests

    public function testRemoveContactsInteger()
    {
        $this->amoLead->contacts = [ 'id' => [ 12345678 ] ];
        $this->amoLead->removeContacts(12345678);
        $this->assertEquals([], $this->amoLead->contacts);
        $this->assertEquals([ 'contacts_id' => [ 12345678 ] ], $this->amoLead->unlink);
    }

    public function testRemoveContactsArray()
    {
        $this->amoLead->contacts = [ 'id' => [ 12345678, 12345679, 12345670, 12345671, 12345672 ] ];
        $this->amoLead->removeContacts([ 12345679, 12345671 ] );
        $this->assertEquals([ 'id' => [ 12345678, 12345670, 12345672 ] ], $this->amoLead->contacts);
        $this->assertEquals([ 'contacts_id' => [ 12345679, 12345671 ] ], $this->amoLead->unlink);
    }

    public function testAddAndRemoveContacts()
    {
        $this->amoLead->contacts = [ 'id' => [ 12345678 ] ];
        $this->amoLead->addContacts([ 12345671, 12345672, 12345673 ]);
        $this->amoLead->removeContacts(12345672);
        $this->amoLead->addContacts(12345672);
        $this->assertEquals([ 'id' => [ 12345678, 12345671, 12345673, 12345672 ] ], $this->amoLead->contacts);
        $this->assertEquals([], $this->amoLead->unlink);
    }

    public function testRemoveAndAddContacts()
    {
        $this->amoLead->contacts = [ 'id' => [ 12345678 ] ];
        $this->amoLead->removeContacts(12345678);
        $this->amoLead->addContacts([ 12345671, 12345672, 12345678, 12345673 ]);
        $this->assertEquals([ 'id' => [ 12345671, 12345672, 12345678, 12345673 ] ], $this->amoLead->contacts);
        $this->assertEquals([], $this->amoLead->unlink);
    }

    // addCompany() tests

    public function testAddCompany()
    {
        $this->amoLead->addCompany(12345678);
        $this->assertEquals([ 'id' => 12345678 ], $this->amoLead->company);
    }

    public function testAddCompanyTwice()
    {
        $this->amoLead->addCompany(12345678);
        $this->amoLead->addCompany(12345672);
        $this->assertEquals([ 'id' => 12345672 ], $this->amoLead->company);
    }

    // removeCompany() tests

    public function testRemoveCompany()
    {
        $this->amoLead->company = [ 'id' => 12345678 ];
        $this->amoLead->removeCompany(12345678);
        $this->assertEquals([], $this->amoLead->company);
        $this->assertEquals([ 'company_id' => 12345678 ], $this->amoLead->unlink);
    }

    public function testAddAndRemoveCompany()
    {
        $this->amoLead->company = [ 'id' => 12345678 ];
        $this->amoLead->addCompany(12345672);
        $this->amoLead->removeCompany(12345678);
        $this->assertEquals([ 'id' => 12345672 ], $this->amoLead->company);
        $this->assertEquals([ 'company_id' => 12345678 ], $this->amoLead->unlink);
    }

    public function testRemoveAndAddCompany()
    {
        $this->amoLead->company = [ 'id' => 12345678 ];
        $this->amoLead->removeCompany(12345678);
        $this->amoLead->addCompany(12345678);
        $this->assertEquals([ 'id' => 12345678 ], $this->amoLead->company);
        $this->assertEquals([], $this->amoLead->unlink);
    }
}
