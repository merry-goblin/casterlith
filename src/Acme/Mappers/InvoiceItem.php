<?php

namespace Acme\Mappers;

use Monolith\Casterlith\Entity\EntityInterface;
use Monolith\Casterlith\Mapper\AbstractMapper;
use Monolith\Casterlith\Mapper\MapperInterface;
use Monolith\Casterlith\Relations\OneToMany;
use Monolith\Casterlith\Relations\ManyToOne;

use Acme\Mappers\Invoice as InvoiceMapper;
use Acme\Mappers\Track as TrackMapper;

class InvoiceItem extends AbstractMapper implements MapperInterface
{
	protected static $table      = 'invoice_items';
	protected static $entity     = 'Acme\Entities\InvoiceItem';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'InvoiceItemId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'InvoiceItemId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'InvoiceId'      => array('type' => 'integer'),
				'TrackId'        => array('type' => 'integer'),
				'UnitPrice'      => array('type' => 'numeric'),
				'Quantity'       => array('type' => 'integer'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'invoice'  => new ManyToOne(new InvoiceMapper(), 'item', 'invoice', '`item`.InvoiceId = `invoice`.InvoiceId', 'items'),
				'track'    => new ManyToOne(new TrackMapper(), 'item', 'track', '`item`.TrackId = `track`.TrackId', 'invoiceItems'),
			);
		}

		return self::$relations;
	}
}
