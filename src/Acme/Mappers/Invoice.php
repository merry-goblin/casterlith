<?php

namespace Acme\Mappers;

use Monolith\Casterlith\Entity\EntityInterface;
use Monolith\Casterlith\Mapper\AbstractMapper;
use Monolith\Casterlith\Mapper\MapperInterface;
use Monolith\Casterlith\Relations\OneToMany;
use Monolith\Casterlith\Relations\ManyToOne;

use Acme\Mappers\Customer as CustomerMapper;
use Acme\Mappers\InvoiceItem as InvoiceItemMapper;

class Invoice extends AbstractMapper implements MapperInterface
{
	protected static $table      = 'invoices';
	protected static $entity     = 'Acme\Entities\Invoice';
	protected static $fields     = array(
		'InvoiceId'          => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
		'CustomerId'         => array('type' => 'integer'),
		'InvoiceDate'        => array('type' => 'datetime'),
		'BillingAddress'     => array('type' => 'string'),
		'BillingCity'        => array('type' => 'string'),
		'BillingState'       => array('type' => 'string'),
		'BillingCountry'     => array('type' => 'string'),
		'BillingPostalCode'  => array('type' => 'string'),
		'Total'              => array('type' => 'string'),
	);
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'InvoiceId';
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'customer'  => new ManyToOne(new CustomerMapper(), 'invoice', 'customer', '`invoice`.CustomerId = `customer`.CustomerId', 'invoices'),
				'items'     => new OneToMany(new InvoiceItemMapper(), 'invoice', 'item', '`invoice`.InvoiceId = `item`.InvoiceId', 'invoice'),
			);
		}

		return self::$relations;
	}
}
