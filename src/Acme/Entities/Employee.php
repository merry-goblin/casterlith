<?php

namespace Acme\Entities;

use Monolith\Casterlith\Casterlith;
use Monolith\Casterlith\Entity\EntityInterface;

class Employee implements EntityInterface
{
	public $EmployeeId  = null;
	public $FirstName   = null;
	public $LastName    = null;
	public $Title       = null;
	public $ReportsTo   = null;
	public $BirthDate   = null;
	public $HireDate    = null;
	public $Address     = null;
	public $City        = null;
	public $State       = null;
	public $Country     = null;
	public $PostalCode  = null;
	public $Phone       = null;
	public $Fax         = null;
	public $Email       = null;

	public $customers     = Casterlith::NOT_LOADED;
	public $reportsTo     = Casterlith::NOT_LOADED;
	public $isReportedBy  = Casterlith::NOT_LOADED;
}
