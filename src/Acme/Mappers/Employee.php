<?php

namespace Acme\Mappers;

use Monolith\Casterlith\Entity\EntityInterface;
use Monolith\Casterlith\Mapper\AbstractMapper;
use Monolith\Casterlith\Mapper\MapperInterface;
use Monolith\Casterlith\Relations\OneToMany;
use Monolith\Casterlith\Relations\ManyToOne;

use Acme\Mappers\Customer as CustomerMapper;
use Acme\Mappers\Employee as EmployeeMapper;

class Employee extends AbstractMapper implements MapperInterface
{
	protected static $table      = 'employees';
	protected static $entity     = 'Acme\Entities\Employee';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'EmployeeId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'EmployeeId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'FirstName'   => array('type' => 'string'),
				'LastName'    => array('type' => 'string'),
				'Title'       => array('type' => 'string'),
				'ReportsTo'   => array('type' => 'string'),
				'BirthDate'   => array('type' => 'string'),
				'HireDate'    => array('type' => 'string'),
				'Address'     => array('type' => 'string'),
				'City'        => array('type' => 'string'),
				'State'       => array('type' => 'string'),
				'Country'     => array('type' => 'string'),
				'PostalCode'  => array('type' => 'string'),
				'Phone'       => array('type' => 'string'),
				'Fax'         => array('type' => 'string'),
				'Email'       => array('type' => 'string'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'customers'     => new OneToMany(new CustomerMapper(), 'employee', 'customer', '`employee`.EmployeeId = `customer`.SupportRepId', 'employee'),
				'reportsTo'     => new OneToMany(new EmployeeMapper(), 'sub', 'sup', '`sub`.ReportsTo = `sup`.EmployeeId', 'isReportedBy'),
				'isReportedBy'  => new OneToMany(new EmployeeMapper(), 'sup', 'sub', '`sup`.EmployeeId = `sub`.ReportsTo', 'reportsTo'),
			);
		}

		return self::$relations;
	}

	/**
	 * @param  string  $relName
	 * @return Merry\Core\Services\Orm\Casterlith\Relations\RelationInterface
	 */
	public static function getRelation($relName)
	{
		if (is_null(self::$relations)) {
			self::getRelations();
		}

		return self::$relations[$relName];
	}
}
