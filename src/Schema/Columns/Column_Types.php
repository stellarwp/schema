<?php
/**
 * The class for the column types.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns;

/**
 * Class Column_Types
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */
final class Column_Types {
	/**
	 * The column type for a string.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const TINYINT = 'tinyint';

	/**
	 * The column type for a smallint.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const SMALLINT = 'smallint';

	/**
	 * The column type for a integer.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const INT = 'int';

	/**
	 * The column type for a mediumint.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const MEDIUMINT = 'mediumint';

	/**
	 * The column type for a bigint.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const BIGINT = 'bigint';

	/**
	 * The column type for a decimal.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const DECIMAL = 'decimal';

	/**
	 * The column type for a float.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const FLOAT = 'float';

	/**
	 * The column type for a double.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const DOUBLE = 'double';

	/**
	 * The column type for a real.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const REAL = 'real';

	/**
	 * The column type for a bit.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const BIT = 'bit';

	/**
	 * The column type for a boolean.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const BOOLEAN = 'boolean';

	/**
	 * The column type for a serial.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const SERIAL = 'serial';

	/**
	 * The column type for a date.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const DATE = 'date';

	/**
	 * The column type for a datetime.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const DATETIME = 'datetime';

	/**
	 * The column type for a timestamp.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const TIMESTAMP = 'timestamp';

	/**
	 * The column type for a time.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const TIME = 'time';

	/**
	 * The column type for a year.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const YEAR = 'year';

	/**
	 * The column type for a char.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const CHAR = 'char';

	/**
	 * The column type for a varchar.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const VARCHAR = 'varchar';

	/**
	 * The column type for a tiny text.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const TINYTEXT = 'tinytext';

	/**
	 * The column type for a text.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const TEXT = 'text';

	/**
	 * The column type for a medium text.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const MEDIUMTEXT = 'mediumtext';

	/**
	 * The column type for a longtext.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const LONGTEXT = 'longtext';

	/**
	 * The column type for a binary.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const BINARY = 'binary';

	/**
	 * The column type for a varbinary.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const VARBINARY = 'varbinary';

	/**
	 * The column type for a tiny blob.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const TINYBLOB = 'tinyblob';

	/**
	 * The column type for a blob.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const BLOB = 'blob';

	/**
	 * The column type for a medium blob.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const MEDIUMBLOB = 'mediumblob';

	/**
	 * The column type for a long blob.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const LONGBLOB = 'longblob';

	/**
	 * The column type for a ENUM.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const ENUM = 'enum';

	/**
	 * The column type for a set.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const SET = 'set';

	/**
	 * The column type for a JSON.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const JSON = 'json';

	/**
	 * The column types that are supported.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SUPPORTED = [
		self::TINYINT,
		self::SMALLINT,
		self::INT,
		self::MEDIUMINT,
		self::BIGINT,
		self::DECIMAL,
		self::FLOAT,
		self::DOUBLE,
		self::REAL,
		self::CHAR,
		self::VARCHAR,
		self::BINARY,
		self::VARBINARY,
		self::BIT,
		self::BOOLEAN,
		self::SERIAL,
		self::DATE,
		self::DATETIME,
		self::TIMESTAMP,
		self::TIME,
		self::YEAR,
		self::TINYTEXT,
		self::TEXT,
		self::MEDIUMTEXT,
		self::LONGTEXT,
		self::BINARY,
		self::VARBINARY,
		self::TINYBLOB,
		self::BLOB,
		self::MEDIUMBLOB,
		self::LONGBLOB,
		self::ENUM,
		self::SET,
		self::JSON,
	];

	/**
	 * The column types that are supported for integer columns.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SUPPORTED_INTEGER = [
		self::TINYINT,
		self::SMALLINT,
		self::INT,
		self::MEDIUMINT,
		self::BIGINT,
	];

	/**
	 * The column types that are supported for float columns.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SUPPORTED_FLOAT = [
		self::FLOAT,
		self::DECIMAL,
		self::DOUBLE,
		self::REAL,
	];

	/**
	 * The column types that are supported for boolean columns.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SUPPORTED_BOOLEAN = [
		self::BOOLEAN,
		self::BIT,
	];

	/**
	 * The column types that are supported for datetime columns.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SUPPORTED_DATETIME = [
		self::DATE,
		self::DATETIME,
		self::TIMESTAMP,
	];

	/**
	 * The column types that are supported for string columns.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SUPPORTED_STRING = [
		self::CHAR,
		self::VARCHAR,
	];

	/**
	 * The column types that are supported for text columns.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SUPPORTED_TEXT = [
		self::TINYTEXT,
		self::TEXT,
		self::MEDIUMTEXT,
		self::LONGTEXT,
	];

	/**
	 * The column types that are supported for binary columns.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SUPPORTED_BINARY = [
		self::BINARY,
		self::VARBINARY,
		self::TINYBLOB,
		self::BLOB,
		self::MEDIUMBLOB,
		self::LONGBLOB,
	];

	/**
	 * The column types that are supported for enum columns.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SUPPORTED_ENUM = [
		self::ENUM,
		self::SET,
	];

	/**
	 * The column types that are supported for json columns.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SUPPORTED_JSON = [
		self::TEXT,
		self::MEDIUMTEXT,
		self::LONGTEXT,
		self::JSON,
	];
}
