<?php

namespace Acme\Entities;

use Monolith\Casterlith\Casterlith;
use Monolith\Casterlith\Entity\EntityInterface;

class InvoiceItem implements EntityInterface
{
	public $InvoiceItemId  = null;
	public $InvoiceId      = null;
	public $TrackId        = null;
	public $UnitPrice      = null;
	public $Quantity       = null;

	public $invoice  = Casterlith::NOT_LOADED;
	public $track    = Casterlith::NOT_LOADED;
}
