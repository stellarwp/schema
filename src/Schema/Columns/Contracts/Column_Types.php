<?php
/**
 * The interface for the column types.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns\Contracts;

/**
 * Interface Column_Types
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns\Contracts
 */
interface Column_Types {
	/**
	 * The column type for a string.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_TINYINT = 'tinyint';

	/**
	 * The column type for a smallint.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_SMALLINT = 'smallint';

	/**
	 * The column type for a integer.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_INT = 'int';

	/**
	 * The column type for a mediumint.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_MEDIUMINT = 'mediumint';

	/**
	 * The column type for a bigint.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_BIGINT = 'bigint';

	/**
	 * The column type for a decimal.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_DECIMAL = 'decimal';

	/**
	 * The column type for a float.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_FLOAT = 'float';

	/**
	 * The column type for a double.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_DOUBLE = 'double';

	/**
	 * The column type for a real.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_REAL = 'real';

	/**
	 * The column type for a bit.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_BIT = 'bit';

	/**
	 * The column type for a boolean.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_BOOLEAN = 'boolean';

	/**
	 * The column type for a serial.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_SERIAL = 'serial';

	/**
	 * The column type for a date.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_DATE = 'date';

	/**
	 * The column type for a datetime.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_DATETIME = 'datetime';

	/**
	 * The column type for a timestamp.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_TIMESTAMP = 'timestamp';

	/**
	 * The column type for a time.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_TIME = 'time';

	/**
	 * The column type for a year.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_YEAR = 'year';

	/**
	 * The column type for a char.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_CHAR = 'char';

	/**
	 * The column type for a varchar.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_VARCHAR = 'varchar';

	/**
	 * The column type for a tiny text.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_TINYTEXT = 'tinytext';

	/**
	 * The column type for a text.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_TEXT = 'text';

	/**
	 * The column type for a medium text.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_MEDIUMTEXT = 'mediumtext';

	/**
	 * The column type for a longtext.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_LONGTEXT = 'longtext';

	/**
	 * The column type for a binary.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_BINARY = 'binary';

	/**
	 * The column type for a varbinary.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_VARBINARY = 'varbinary';

	/**
	 * The column type for a tiny blob.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_TINYBLOB = 'tinyblob';

	/**
	 * The column type for a blob.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_BLOB = 'blob';

	/**
	 * The column type for a medium blob.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_MEDIUMBLOB = 'mediumblob';

	/**
	 * The column type for a long blob.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_LONGBLOB = 'longblob';

	/**
	 * The column type for a ENUM.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_ENUM = 'enum';

	/**
	 * The column type for a set.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_SET = 'set';

	/**
	 * The column type for a JSON.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const COLUMN_TYPE_JSON = 'json';

	/**
	 * The column types that are supported.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SUPPORTED_COLUMN_TYPES = [
		self::COLUMN_TYPE_TINYINT,
		self::COLUMN_TYPE_SMALLINT,
		self::COLUMN_TYPE_INT,
		self::COLUMN_TYPE_MEDIUMINT,
		self::COLUMN_TYPE_BIGINT,
		self::COLUMN_TYPE_DECIMAL,
		self::COLUMN_TYPE_FLOAT,
		self::COLUMN_TYPE_DOUBLE,
		self::COLUMN_TYPE_REAL,
		self::COLUMN_TYPE_CHAR,
		self::COLUMN_TYPE_VARCHAR,
		self::COLUMN_TYPE_BINARY,
		self::COLUMN_TYPE_VARBINARY,
		self::COLUMN_TYPE_BIT,
		self::COLUMN_TYPE_BOOLEAN,
		self::COLUMN_TYPE_SERIAL,
		self::COLUMN_TYPE_DATE,
		self::COLUMN_TYPE_DATETIME,
		self::COLUMN_TYPE_TIMESTAMP,
		self::COLUMN_TYPE_TIME,
		self::COLUMN_TYPE_YEAR,
		self::COLUMN_TYPE_TINYTEXT,
		self::COLUMN_TYPE_TEXT,
		self::COLUMN_TYPE_MEDIUMTEXT,
		self::COLUMN_TYPE_LONGTEXT,
		self::COLUMN_TYPE_BINARY,
		self::COLUMN_TYPE_VARBINARY,
		self::COLUMN_TYPE_TINYBLOB,
		self::COLUMN_TYPE_BLOB,
		self::COLUMN_TYPE_MEDIUMBLOB,
		self::COLUMN_TYPE_LONGBLOB,
		self::COLUMN_TYPE_ENUM,
		self::COLUMN_TYPE_SET,
		self::COLUMN_TYPE_JSON,
	];

	public const SUPPORTED_INTEGER_COLUMN_TYPES = [
		self::COLUMN_TYPE_TINYINT,
		self::COLUMN_TYPE_SMALLINT,
		self::COLUMN_TYPE_INT,
		self::COLUMN_TYPE_MEDIUMINT,
		self::COLUMN_TYPE_BIGINT,
	];

	public const SUPPORTED_FLOAT_COLUMN_TYPES = [
		self::COLUMN_TYPE_FLOAT,
		self::COLUMN_TYPE_DECIMAL,
		self::COLUMN_TYPE_DOUBLE,
		self::COLUMN_TYPE_REAL,
	];

	public const SUPPORTED_BOOLEAN_COLUMN_TYPES = [
		self::COLUMN_TYPE_BOOLEAN,
		self::COLUMN_TYPE_BIT,
	];

	public const SUPPORTED_DATETIME_COLUMN_TYPES = [
		self::COLUMN_TYPE_DATE,
		self::COLUMN_TYPE_DATETIME,
		self::COLUMN_TYPE_TIMESTAMP,
	];

	public const SUPPORTED_STRING_COLUMN_TYPES = [
		self::COLUMN_TYPE_CHAR,
		self::COLUMN_TYPE_VARCHAR,
	];

	public const SUPPORTED_TEXT_COLUMN_TYPES = [
		self::COLUMN_TYPE_TINYTEXT,
		self::COLUMN_TYPE_TEXT,
		self::COLUMN_TYPE_MEDIUMTEXT,
		self::COLUMN_TYPE_LONGTEXT,
	];

	public const SUPPORTED_BINARY_COLUMN_TYPES = [
		self::COLUMN_TYPE_BINARY,
		self::COLUMN_TYPE_VARBINARY,
		self::COLUMN_TYPE_TINYBLOB,
		self::COLUMN_TYPE_BLOB,
		self::COLUMN_TYPE_MEDIUMBLOB,
		self::COLUMN_TYPE_LONGBLOB,
	];

	public const SUPPORTED_ENUM_COLUMN_TYPES = [
		self::COLUMN_TYPE_ENUM,
		self::COLUMN_TYPE_SET,
	];

	public const SUPPORTED_JSON_COLUMN_TYPES = [
		self::COLUMN_TYPE_TEXT,
		self::COLUMN_TYPE_MEDIUMTEXT,
		self::COLUMN_TYPE_LONGTEXT,
		self::COLUMN_TYPE_JSON,
	];
}
