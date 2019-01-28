<?php

namespace Acme\Mappers;

use Monolith\Casterlith\Entity\EntityInterface;
use Monolith\Casterlith\Mapper\AbstractMapper;
use Monolith\Casterlith\Mapper\MapperInterface;
use Monolith\Casterlith\Relations\OneToMany;
use Monolith\Casterlith\Relations\ManyToOne;

use Acme\Mappers\Employee as EmployeeMapper;
use Acme\Mappers\Invoice as InvoiceMapper;

class Customer extends AbstractMapper implements MapperInterface
{
	protected static $table      = 'customers';
	protected static $entity     = 'Acme\Entities\Customer';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'CustomerId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'CustomerId'    => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'FirstName'     => array('type' => 'string'),
				'LastName'      => array('type' => 'string'),
				'Company'       => array('type' => 'string'),
				'LastName'      => array('type' => 'string'),
				'Address'       => array('type' => 'string'),
				'City'          => array('type' => 'string'),
				'State'         => array('type' => 'string'),
				'Country'       => array('type' => 'string'),
				'PostalCode'    => array('type' => 'string'),
				'Phone'         => array('type' => 'string'),
				'Fax'           => array('type' => 'string'),
				'Email'         => array('type' => 'string'),
				'SupportRepId'  => array('type' => 'integer'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'employee' => new ManyToOne(new EmployeeMapper(), 'c', 'e', '`c`.SupportRepId = `e`.EmployeeId', 'customers'),
				'invoices' => new OneToMany(new InvoiceMapper(), 'c', 'i', '`c`.CustomerId = `i`.CustomerId', 'customer'),
			);
		}

		return self::$relations;
	}
}
