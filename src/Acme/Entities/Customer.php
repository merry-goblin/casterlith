<?php

namespace Acme\Entities;

use Monolith\Casterlith\Casterlith;
use Monolith\Casterlith\Entity\EntityInterface;

class Customer implements EntityInterface
{
	public $CustomerId       = null;
	public $FirstName        = null;
	public $LastName         = null;
	public $Company          = null;
	public $Address          = null;
	public $City             = null;
	public $State            = null;
	public $Country          = null;
	public $PostalCode       = null;
	public $Phone            = null;
	public $Fax              = null;
	public $Email            = null;
	public $SupportRepId     = null;

	public $employee  = Casterlith::NOT_LOADED;
	public $invoices  = Casterlith::NOT_LOADED;
}
