<?php

namespace Acme\Entities;

use Monolith\Casterlith\Casterlith;
use Monolith\Casterlith\Entity\EntityInterface;

class Invoice implements EntityInterface
{
	public $InvoiceId          = null;
	public $CustomerId         = null;
	public $InvoiceDate        = null;
	public $BillingAddress     = null;
	public $BillingCity        = null;
	public $BillingState       = null;
	public $BillingCountry     = null;
	public $BillingPostalCode  = null;
	public $Total              = null;

	public $customer  = Casterlith::NOT_LOADED;
	public $items     = Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->InvoiceId;
	}
}
