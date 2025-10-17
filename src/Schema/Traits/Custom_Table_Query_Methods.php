<?php
/**
 * Provides query methods common to all custom tables.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Traits;
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Traits;

use DateTime;
use DateTimeInterface;
use Exception;
use Generator;
use InvalidArgumentException;
use StellarWP\Schema\Columns\Contracts\Column;
use StellarWP\Schema\Columns\Contracts\Auto_Incrementable;
use StellarWP\Schema\Columns\PHP_Types;
use StellarWP\Schema\Config;

/**
 * Trait Custom_Table_Query_Methods.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Traits;
 */
trait Custom_Table_Query_Methods {
	/**
	 * Fetches all the rows from the table using a batched query.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $batch_size   The number of rows to fetch per batch.
	 * @param string $output       The output type of the query, one of OBJECT, ARRAY_A, or ARRAY_N.
	 * @param string $where_clause The optional WHERE clause to use.
	 * @param string $order_by     The optional ORDER BY clause to use.
	 *
	 * @return Generator<array<string, mixed>> The rows from the table.
	 */
	protected static function fetch_all( int $batch_size = 50, string $output = OBJECT, string $where_clause = '', string $order_by = '' ): Generator {
		$fetched  = 0;
		$total    = null;
		$offset   = 0;
		$database = Config::get_db();

		do {
			$primary_columns = static::primary_columns();

			$order_by = $order_by ?: implode( ', ', array_map( fn( $column ) => "{$column} ASC", $primary_columns ) );

			$query = $database::prepare(
				"SELECT * FROM %i {$where_clause} ORDER BY {$order_by} LIMIT %d, %d",
				static::table_name( true ),
				$offset,
				$batch_size
			);

			$batch = $database::get_results(
				$query,
				$output
			);

			// We need to get the total number of rows, only after the first batch.
			$total  ??= $database::get_var( $database::prepare( "SELECT COUNT(*) FROM %i {$where_clause}", static::table_name( true ) ) );
			$fetched += count( $batch );

			$offset += $batch_size;

			yield from $batch;
		} while ( $fetched < $total );
	}

	/**
	 * Fetches all the rows from the table using a batched query.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $batch_size   The number of rows to fetch per batch.
	 * @param string $where_clause The optional WHERE clause to use.
	 * @param string $order_by     The optional ORDER BY clause to use.
	 *
	 * @return Generator<array<string, mixed>> The rows from the table.
	 */
	public static function get_all( int $batch_size = 50, string $where_clause = '', string $order_by = '' ): Generator {
		$batch = static::fetch_all( $batch_size, ARRAY_A, $where_clause, $order_by );

		foreach ( $batch as $row ) {
			yield static::transform_from_array( self::amend_value_types( $row ) );
		}
	}

	/**
	 * Inserts a single row into the table.
	 *
	 * @since 3.0.0
	 *
	 * @param array<mixed> $entry The entry to insert.
	 *
	 * @return bool|int The number of rows affected, or `false` on failure.
	 */
	public static function insert( array $entry ) {
		return static::insert_many( [ $entry ] );
	}

	/**
	 * Updates a single row in the table.
	 *
	 * @since 3.0.0
	 *
	 * @param array<mixed> $entry The entry to update.
	 *
	 * @return bool Whether the update was successful.
	 */
	public static function update_single( array $entry ): bool {
		return static::update_many( [ $entry ] );
	}

	/**
	 * Inserts or updates a single row in the table.
	 *
	 * @since 3.0.0
	 *
	 * @param array<mixed> $entry The entry to upsert.
	 *
	 * @return bool Whether the upsert was successful.
	 */
	public static function upsert( array $entry ): bool {
		$primary_columns = static::primary_columns();
		$primary_values  = array_filter( array_intersect_key( $entry, array_flip( $primary_columns ) ) );

		$is_update = count( $primary_values ) === count( $primary_columns );

		return $is_update ? static::update_single( $entry ) : (bool) static::insert( $entry );
	}

	/**
	 * Inserts multiple rows into the table.
	 *
	 * @since 3.0.0
	 *
	 * @param array<mixed> $entries The entries to insert.
	 *
	 * @return bool|int The number of rows affected, or `false` on failure.
	 */
	public static function insert_many( array $entries ) {
		[ $prepared_columns, $prepared_values ] = static::prepare_statements_values( $entries );

		$database = Config::get_db();

		return $database::query(
			$database::prepare(
				"INSERT INTO %i ({$prepared_columns}) VALUES {$prepared_values}",
				static::table_name( true ),
			)
		);
	}

	/**
	 * Deletes a single row from the table.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $uid    The ID of the row to delete.
	 * @param string $column The column to use for the delete query.
	 *
	 * @return bool Whether the delete was successful.
	 */
	public static function delete( int $uid, string $column = '' ): bool {
		return (bool) static::delete_many( [ $uid ], $column );
	}

	/**
	 * Deletes multiple rows from the table.
	 *
	 * @since 3.0.0
	 *
	 * @param array<int|string> $ids        The IDs of the rows to delete.
	 * @param string            $column     The column to use for the delete query.
	 * @param string            $more_where The more WHERE clause to use for the delete query.
	 *
	 * @return bool|int The number of rows affected, or `false` on failure.
	 */
	public static function delete_many( array $ids, string $column = '', string $more_where = '' ) {
		$ids = array_filter(
			array_map(
				fn( $id ) => is_numeric( $id ) ? (int) $id : "'{$id}'",
				$ids
			)
		);

		if ( empty( $ids ) ) {
			return false;
		}

		$database     = Config::get_db();
		$prepared_ids = implode( ', ', $ids );
		$column       = $column ?
			"{$column} IN ({$prepared_ids})" :
			implode(
				' AND ',
				array_map(
					function ( $c ) use ( $prepared_ids ) {
						return "{$c} IN ({$prepared_ids})";
					},
				static::primary_columns()
				)
			);

		return $database::query(
			$database::prepare(
				"DELETE FROM %i WHERE {$column} {$more_where}",
				static::table_name( true ),
			)
		);
	}
	/**
	 * Prepares the statements and values for the insert and update queries.
	 *
	 * @since 3.0.0
	 *
	 * @param array<mixed> $entries The entries to prepare.
	 *
	 * @return array<string> The prepared statements and values.
	 */
	protected static function prepare_statements_values( array $entries ): array {
		$uid_column    = static::uid_column();
		$column_object = static::get_columns()->get( $uid_column );

		$entries = array_map(
			function ( $entry ) use ( $uid_column, $column_object ) {
				if ( ! ( $column_object instanceof Auto_Incrementable && $column_object->get_auto_increment() ) ) {
					return $entry;
				}

				unset( $entry[ $uid_column ] );
				return $entry;
			},
			$entries
		);

		$columns = static::get_columns();

		$database = Config::get_db();
		$columns          = array_keys( $entries[0] );
		$prepared_columns = implode(
			', ',
			array_map(
				static fn( string $column ) => "`$column`",
				$columns
			)
		);

		$prepared_values = [];
		foreach ( $entries as $row_index => $entry ) {
			$prepared_values[ $row_index ] = [];
			foreach ( $entry as $column => $value ) {
				[ $prepared_value, $placeholder ] = self::prepare_value_for_query( $column, $value );
				$prepared_values[ $row_index ][] = 'NULL' === $placeholder ? $placeholder : $database::prepare( $placeholder, $prepared_value );
			}
		}

		$prepared_values = implode( ', ',
			array_map(
				static fn ( array $entry ) => '(' . implode( ', ', $entry ) . ')',
				$prepared_values
			)
		);

		return [ $prepared_columns, $prepared_values ];
	}

	/**
	 * Fetches all the rows from the table using a batched query and a WHERE clause.
	 *
	 * @since 3.0.0
	 *
	 * @param string $where_clause The WHERE clause to use.
	 * @param int    $batch_size   The number of rows to fetch per batch.
	 * @param string $output       The output type of the query, one of OBJECT, ARRAY_A, or ARRAY_N.
	 * @param string $order_by     The optional ORDER BY clause to use.
	 *
	 * @return Generator<array<string, mixed>> The rows from the table.
	 */
	protected static function fetch_all_where( string $where_clause, int $batch_size = 50, string $output = OBJECT, string $order_by = '' ): Generator {
		return static::fetch_all( $batch_size, $output, $where_clause, $order_by );
	}

	/**
	 * Fetches the first row from the table using a WHERE clause.
	 *
	 * @since 3.0.0
	 *
	 * @param string $where_clause The prepared WHERE clause to use.
	 * @param string $output       The output type of the query, one of OBJECT, ARRAY_A, or ARRAY_N.
	 *
	 * @return array|object|null The row from the table, or `null` if no row was found.
	 */
	protected static function fetch_first_where( string $where_clause, string $output = OBJECT ) {
		$database = Config::get_db();

		return $database::get_row(
			$database::prepare(
				"SELECT * FROM %i {$where_clause} LIMIT 1",
				static::table_name( true )
			),
			$output
		);
	}

	/**
	 * Gets the total number of items in the table.
	 *
	 * @since 3.0.0
	 *
	 * @param array<string,mixed> $args The query arguments.
	 *
	 * @return int The total number of items in the table.
	 */
	public static function get_total_items( array $args = [] ): int {
		$database = Config::get_db();
		$where    = static::build_where_from_args( $args );

		return (int) $database::get_var(
			$database::prepare(
				"SELECT COUNT(*) FROM %i a {$where}",
				static::table_name( true )
			)
		);
	}

	/**
	 * Updates multiple rows into the table.
	 *
	 * @since 3.0.0
	 * @since 3.1.4 Enabled unfolding the value if is an array.
	 *
	 * @param array<array<string, null|int|string|float|bool|DateTimeInterface|string[]|int[]|float[]|DateTimeInterface[]>> $entries The entries to update.
	 *
	 * @return bool Whether the update was successful.
	 */
	public static function update_many( array $entries ): bool {
		$uid_column = static::uid_column();

		$database = Config::get_db();

		$queries = [];
		$columns = static::get_columns()->get_names();
		foreach ( $entries as $entry ) {
			$uid = $entry[ $uid_column ] ?? '';

			if ( ! $uid ) {
				continue;
			}

			$set_statement = [];

			foreach ( $entry as $column => $value ) {
				if ( $column === $uid_column ) {
					continue;
				}

				if ( ! in_array( $column, $columns, true ) ) {
					continue;
				}

				if ( $value instanceof DateTimeInterface ) {
					$value = $value->format( 'Y-m-d H:i:s' );
				}

				[ $value, $placeholder ] = self::prepare_value_for_query( $column, $value );

				$set_statement[] = $database::prepare( "%i = {$placeholder}", ...array_filter( [ $column, ...self::ensure_array( $value ) ], static fn( $v ) => null !== $v ) );
			}

			$set_statement = implode( ', ', $set_statement );

			$queries[] = $database::prepare(
				"UPDATE %i SET {$set_statement} WHERE {$uid_column} = %s;",
				static::table_name( true ),
				$uid
			);
		}

		$database::beginTransaction();

		$results = [];

		foreach ( $queries as $query ) {
			$results[] = $database::query( $query );
		}

		$all_good = count( array_filter( $results, static fn( $result ) => $result !== false ) ) === count( $results );

		if ( ! $all_good ) {
			$database::rollBack();
			return false;
		}

		$database::commit();
		return true;
	}

	/**
	 * Method used to paginate the results of a query.
	 *
	 * Also supports joining another table.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $args                      The query arguments.
	 * @param int    $per_page                  The number of items to display per page.
	 * @param int    $page                      The current page number.
	 * @param array  $columns                   The columns to select.
	 * @param string $join_table                The table to join.
	 * @param string $join_condition            The condition to join on.
	 * @param array  $selectable_joined_columns The columns from the joined table to select.
	 *
	 * @return array The items.
	 * @throws InvalidArgumentException If the table to join is the same as the current table.
	 *                                  If the join condition does not contain an equal sign.
	 *                                  If the join condition does not contain valid columns.
	 */
	public static function paginate( array $args, int $per_page = 20, int $page = 1, array $columns = [ '*' ], string $join_table = '', string $join_condition = '', array $selectable_joined_columns = [] ): array {
		$is_join = (bool) $join_table;

		if ( $is_join && static::table_name( true ) === $join_table::table_name( true ) ) {
			throw new InvalidArgumentException( 'The table to join must be different from the current table.' );
		}

		$per_page = min( max( 1, $per_page ), 200 );
		$page     = max( 1, $page );

		$offset      = ( $page - 1 ) * $per_page;
		$args_offset = $args['offset'] ?? $offset;
		$offset      = 1 === $page ? $args_offset : $offset;

		$orderby = $args['orderby'] ?? static::uid_column();
		$order   = strtoupper( $args['order'] ?? 'ASC' );

		$column_names = static::get_columns()->get_names();

		if ( ! in_array( $orderby, $column_names, true ) ) {
			$orderby = static::uid_column();
		}

		if ( ! in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
			$order = 'ASC';
		}

		$where = static::build_where_from_args( $args );

		[ $join, $secondary_columns ] = $is_join ? static::get_join_parts( $join_table, $join_condition, $selectable_joined_columns ) : [ '', '' ];

		sort( $columns );
		sort( $column_names );

		$formatted_columns = implode( ', ', array_map( fn( $column ) => "a.{$column}", $columns ) );

		/**
		 * Fires before the results of the query are fetched.
		 *
		 * @since 3.0.0
		 *
		 * @param array<string,mixed> $args  The query arguments.
		 * @param class-string        $class The class name.
		 */
		do_action( 'tec_common_custom_table_query_pre_results', $args, static::class );

		$database = Config::get_db();

		$results = $database::get_results(
			$database::prepare(
				"SELECT {$formatted_columns}{$secondary_columns} FROM %i a {$join} {$where} ORDER BY a.{$orderby} {$order} LIMIT %d, %d",
				static::table_name( true ),
				$offset,
				$per_page
			),
			ARRAY_A
		);

		$results = array_map( fn( $result ) => self::amend_value_types( $result ), $results );

		if ( [ '*' ] === $columns || $columns === $column_names ) {
			// If we are querying for a full row, let's transform the results.
			$results = array_map( fn( $result ) => static::transform_from_array( $result ), $results );
		}

		/**
		 * Fires after the results of the query are fetched.
		 *
		 * @since 3.0.0
		 *
		 * @param array<mixed>       $results The results of the query.
		 * @param array<string,mixed> $args  The query arguments.
		 * @param class-string        $class The class name.
		 */
		do_action( 'tec_common_custom_table_query_post_results', $results, $args, static::class );

		/**
		 * Filters the results of the query.
		 *
		 * @since 3.0.0
		 *
		 * @param array<mixed>       $results The results of the query.
		 * @param array<string,mixed> $args  The query arguments.
		 * @param class-string        $class The class name.
		 */
		return apply_filters( 'tec_common_custom_table_query_results', $results, $args, static::class );
	}

	/**
	 * Builds a WHERE clause from the provided arguments.
	 *
	 * @since 3.0.0
	 *
	 * @param array<string,mixed> $args   The query arguments.
	 *
	 * @return string The WHERE clause.
	 */
	protected static function build_where_from_args( array $args = [] ): string {
		$query_operator = strtoupper( $args['query_operator'] ?? 'AND' );

		if ( ! in_array( $query_operator, [ 'AND', 'OR' ], true ) ) {
			$query_operator = 'AND';
		}

		unset( $args['order'], $args['orderby'], $args['query_operator'], $args['offset'] );

		$joined_prefix = 'a.';
		$database      = Config::get_db();

		$where = [];

		$search = $args['term'] ?? '';
		if ( $search ) {
			$searchable_columns = static::get_searchable_columns();

			$search_where = [];

			foreach ( $searchable_columns as $column ) {
				$search_where[] = $database::prepare( "{$joined_prefix}{$column->get_name()} LIKE %s", '%' . $database::esc_like( $search ) . '%' );
			}

			if ( ! empty( $search_where ) ) {
				$where[] = '(' . implode( ' OR ', $search_where ) . ')';
			}
		}

		$columns = static::get_columns()->get_names();

		foreach ( $args as $arg ) {
			if ( ! is_array( $arg ) ) {
				continue;
			}

			if ( empty( $arg['column'] ) ) {
				continue;
			}

			if ( ! in_array( $arg['column'], $columns, true ) ) {
				continue;
			}

			if ( ! isset( $arg['value'] ) ) {
				// We check that the column has any value then.
				$arg['value']    = '';
				$arg['operator'] = '!=';
			}

			if ( empty( $arg['operator'] ) ) {
				$arg['operator'] = '=';
			}

			// For anything else, you should build your own query.
			if ( ! in_array( strtoupper( $arg['operator'] ), array_values( static::operators() ), true ) ) {
				$arg['operator'] = '=';
			}

			$column   = $arg['column'];
			$operator = strtoupper( $arg['operator'] );

			[ $value, $placeholder ] = self::prepare_value_for_query( $column, $arg['value'] );

			$database = Config::get_db();
			$query    = "{$joined_prefix}{$column} {$operator} {$placeholder}";

			if ( is_array( $value ) ) {
				$where[] = $database::prepare( $query, ...$value );
				continue;
			}

			if ( 'NULL' === $placeholder ) {
				$where[] = $query;
				continue;
			}

			$where[] = $database::prepare( $query, $value );
		}

		/**
		 * Filters the WHERE clause.
		 *
		 * @since 3.0.0
		 *
		 * @param array<string>       $where The WHERE clause parts.
		 * @param array<string,mixed> $args  The query arguments.
		 * @param class-string        $class The class name.
		 */
		$where = apply_filters( 'tec_common_custom_table_query_where', array_filter( $where ), $args, static::class );

		if ( empty( $where ) ) {
			return '';
		}

		return 'WHERE ' . implode( " {$query_operator} ", $where );
	}

	/**
	 * Gets the JOIN parts of the query.
	 *
	 * @since 3.0.0
	 *
	 * @param string $join_table                The table to join.
	 * @param string $join_condition            The condition to join on.
	 * @param array  $selectable_joined_columns The columns from the joined table to select.
	 *
	 * @return array<string> The JOIN statement and the secondary columns to select.
	 * @throws InvalidArgumentException If the join condition does not contain an equal sign.
	 *                                  If the join condition does not contain valid columns.
	 */
	protected static function get_join_parts( string $join_table, string $join_condition, array $selectable_joined_columns = [] ): array {
		if ( ! strstr( $join_condition, '=' ) ) {
			throw new InvalidArgumentException( 'The join condition must contain an equal sign.' );
		}

		$join_condition = array_map( 'trim', explode( '=', $join_condition, 2 ) );

		$secondary_table_columns = $join_table::get_columns()->get_names();

		$both_table_columns = array_merge( static::get_columns()->get_names(), $secondary_table_columns );

		if ( ! in_array( $join_condition[0], $both_table_columns, true ) || ! in_array( $join_condition[1], $both_table_columns, true ) ) {
			throw new InvalidArgumentException( 'The join condition must contain valid columns.' );
		}

		$join_condition = 'a.' . str_replace( [ 'a.', 'b.' ], '', $join_condition[0] ) . ' = b.' . str_replace( [ 'a.', 'b.' ], '', $join_condition[1] );

		$clean_secondary_columns = [];

		foreach ( array_map( 'trim', $selectable_joined_columns ) as $column ) {
			if ( ! in_array( $column, $secondary_table_columns, true ) ) {
				continue;
			}

			$clean_secondary_columns[] = 'b.' . $column;
		}

		$database                = Config::get_db();
		$clean_secondary_columns = $clean_secondary_columns ? ', ' . implode( ', ', $clean_secondary_columns ) : '';

		return [
			$database::prepare( "JOIN %i b ON {$join_condition}", $join_table::table_name( true ) ),
			$clean_secondary_columns,
		];
	}

	/**
	 * Gets all models by a column.
	 *
	 * @since 3.0.0
	 * @since 3.1.1 Added the $order_by parameter.
	 * @since 3.1.4 Enabled unfolding the value if is an array.
	 *
	 * @param string                                                                                  $column   The column to get the models by.
	 * @param null|int|string|float|bool|DateTimeInterface|string[]|int[]|float[]|DateTimeInterface[] $value    The value to get the models by.
	 * @param string                                                                                  $operator The operator to use.
	 * @param int                                                                                     $limit    The limit of models to return.
	 * @param string                                                                                  $order_by The order by clause to use.
	 *
	 * @return mixed[] The models, or an empty array if no models are found.
	 *
	 * @throws InvalidArgumentException If the column does not exist.
	 */
	public static function get_all_by( string $column, $value, string $operator = '=', int $limit = 50, string $order_by = '' ): ?array{
		[ $value, $placeholder ] = self::prepare_value_for_query( $column, $value );

		$operator = strtoupper( $operator );

		$database = Config::get_db();
		$results  = [];
		foreach ( static::fetch_all_where( $database::prepare( "WHERE %i {$operator} {$placeholder}", ...array_filter( [ $column, ...self::ensure_array( $value ) ], static fn( $v ) => null !== $v ) ), $limit, ARRAY_A, $order_by ) as $data_array ) {
			if ( empty( $data_array[ static::uid_column() ] ) ) {
				continue;
			}

			$results[] = static::transform_from_array( self::amend_value_types( $data_array ) );
		}

		return $results;
	}

	/**
	 * Gets the first model by a column.
	 *
	 * @since 3.0.0
	 * @since 3.1.4 Enabled unfolding the value if is an array.
	 * @since 3.1.4 Added the $operator parameter.
	 *
	 * @param string                                                                                  $column   The column to get the model by.
	 * @param null|int|string|float|bool|DateTimeInterface|string[]|int[]|float[]|DateTimeInterface[] $value    The value to get the model by.
	 * @param string                                                                                  $operator The operator to use.
	 *
	 * @return ?mixed The model, or `null` if no model is found.
	 *
	 * @throws InvalidArgumentException If the column does not exist.
	 * @throws InvalidArgumentException If the operator is invalid.
	 */
	public static function get_first_by( string $column, $value, string $operator = '=' ) {
		[ $value, $placeholder ] = self::prepare_value_for_query( $column, $value );

		$operator = strtoupper( $operator );

		if ( ! in_array( $operator, self::operators(), true ) ) {
			throw new InvalidArgumentException( "Invalid operator: {$operator}." );
		}

		$database   = Config::get_db();
		$data_array = static::fetch_first_where( $database::prepare( "WHERE %i {$operator} {$placeholder}", ...array_filter( [ $column, ...self::ensure_array( $value ) ], static fn( $v ) => null !== $v ) ), ARRAY_A );

		if ( empty( $data_array[ static::uid_column() ] ) ) {
			return null;
		}

		return static::transform_from_array( self::amend_value_types( $data_array ) );
	}

	/**
	 * Prepares a value for a query.
	 *
	 * @since 3.0.0
	 *
	 * @param string                                                                                           $column The column to prepare the value for.
	 * @param null|string|int|float|bool|DateTimeInterface|array<null|string|int|float|bool|DateTimeInterface> $value  The value to prepare.
	 *
	 * @return array{0: mixed, 1: string} The prepared value and placeholder.
	 *
	 * @throws InvalidArgumentException If the column does not exist.
	 */
	private static function prepare_value_for_query( string $column, $value ): array {
		$columns = static::get_columns();

		/** @var ?Column $column */
		$column = $columns->get( $column );

		if ( ! $column ) {
			throw new InvalidArgumentException( "Column $column does not exist." );
		}

		$column_type = $column->get_php_type();

		if ( null === $value && $column->get_nullable() ) {
			return [ null, 'NULL' ];
		}

		switch ( $column->get_php_type() ) {
			case PHP_Types::INT:
				$value       = is_array( $value ) ? array_map( fn( $v ) => (int) $v, $value ) : (int) $value;
				$placeholder = '%d';
				break;
			case PHP_Types::BOOL:
				$value       = is_array( $value ) ? array_map( fn( $v ) => (int) (bool) $v, $value ) : (int) (bool) $value;
				$placeholder = '%d';
				break;
			case PHP_Types::STRING:
			case PHP_Types::DATETIME:
				$value       = is_array( $value ) ?
					array_map( fn( $v ) => $v instanceof DateTimeInterface ? $v->format( 'Y-m-d H:i:s' ) : (string) $v, $value ) :
					( $value instanceof DateTimeInterface ? $value->format( 'Y-m-d H:i:s' ) : (string) $value );
				$placeholder = '%s';
				break;
			case PHP_Types::JSON:
				$value       = is_string( $value ) ? $value : wp_json_encode( $value );
				$placeholder = '%s';
				break;
			case PHP_Types::FLOAT:
				$value       = is_array( $value ) ? array_map( fn( $v ) => (float) $v, $value ) : (float) $value;
				$placeholder = '%f';
				break;
			case PHP_Types::BLOB:
				// For blob, we store as base64 encoded string.
				if ( is_array( $value ) ) {
					$value = array_map( fn( $v ) => is_string( $v ) ? $v : base64_encode( (string) $v ), $value );
				} else {
					$value = is_string( $value ) ? base64_encode( (string) $value ) : $value;
				}
				$placeholder = '%s';
				break;
			default:
				throw new InvalidArgumentException( "Unsupported column type: $column_type." );
		}

		if ( is_array( $value ) && ! $value ) {
			return [ null, '(NULL)' ];
		}

		// @phpstan-ignore-next-line
		return [ $value, is_array( $value ) ? '(' . implode( ',', array_fill( 0, count( $value ), $placeholder ) ) . ')' : $placeholder ];
	}

	/**
	 * Gets a model by its ID.
	 *
	 * @since 3.0.0
	 *
	 * @param int|string $id The ID.
	 *
	 * @return ?mixed The model, or null if not found.
	 */
	public static function get_by_id( $id ) {
		return static::get_first_by( static::uid_column(), $id );
	}

	/**
	 * Gets the operators supported by the table.
	 *
	 * @since 3.0.0
	 *
	 * @return array<string, string> The operators supported by the table.
	 */
	public static function operators(): array {
		return [
			'eq'     => '=',
			'neq'    => '!=',
			'gt'     => '>',
			'lt'     => '<',
			'gte'    => '>=',
			'lte'    => '<=',
			'in'     => 'IN',
			'not_in' => 'NOT IN',
		];
	}

	/**
	 * Amends the value types of the data.
	 *
	 * @since 3.0.0
	 *
	 * @param array<string, mixed> $data The data.
	 *
	 * @return array<string, mixed> The amended data.
	 *
	 * @throws InvalidArgumentException If the column type is unsupported.
	 * @throws InvalidArgumentException If the datetime value format is invalid.
	 */
	private static function amend_value_types( array $data ): array {
		$columns = static::get_columns();
		$column_names = $columns->get_names();
		foreach ( $data as $column => $value ) {
			if ( ! in_array( $column, $column_names, true ) ) {
				continue;
			}

			$column_object = $columns->get( $column );

			if ( $column_object->get_nullable() && null === $value ) {
				continue;
			}

			$column_php_type = $column_object->get_php_type();

			$data[ $column ] = static::cast_value_based_on_type( $column_php_type, $value );
		}

		return $data;
	}

	/**
	 * Casts a value based on the type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $type The type to cast the value to.
	 * @param mixed  $value The value to cast.
	 *
	 * @return mixed The cast value.
	 */
	public static function cast_value_based_on_type( string $type, $value ) {
		switch ( $type ) {
			case PHP_Types::INT:
				return (int) $value;
			case PHP_Types::STRING:
				return (string) $value;
			case PHP_Types::FLOAT:
				return (float) $value;
			case PHP_Types::BOOL:
				return (bool) $value;
			case PHP_Types::JSON:
				return is_string( $value ) ? (array) json_decode( $value, true ) : (array) $value;
			case PHP_Types::DATETIME:
				if ( $value instanceof DateTimeInterface ) {
					return $value;
				}

				try {
					$instance = Config::get_container()->get( DateTimeInterface::class );
				} catch ( Exception $e ) {
					$instance = DateTime::class;
				}

				$new_value = $instance::createFromFormat( 'Y-m-d H:i:s', $value );

				if ( $new_value instanceof DateTimeInterface ) {
					return $new_value;
				}

				$new_value = $instance::createFromFormat( 'Y-m-d', $value );

				if ( ! $new_value instanceof DateTimeInterface ) {
					throw new InvalidArgumentException( "Invalid datetime value format: {$value}." );
				}

				return $new_value;
			case PHP_Types::BLOB:
				// Decode base64 encoded blob data.
				if ( is_string( $value ) ) {
					return base64_decode( $value );
				}
				return (string) $value;
			default:
				throw new InvalidArgumentException( "Unsupported column type: {$type}." );
		}
	}

	/**
	 * Ensures the value is an array.
	 *
	 * @since 3.1.4
	 *
	 * @param null|string|int|float|bool|DateTimeInterface|string[]|int[]|float[]|DateTimeInterface[] $value The value to ensure is an array.
	 *
	 * @return array<null|string|int|float|bool|DateTimeInterface> The value as an array.
	 */
	private static function ensure_array( $value ): array {
		if ( ! is_int( $value ) && ! is_string( $value ) && ! is_float( $value ) && ! is_bool( $value ) && ! $value instanceof DateTimeInterface && ! is_array( $value ) ) {
			throw new InvalidArgumentException( 'Value should be an integer, string, float, boolean, DateTimeInterface, or array.' );
		}

		if ( is_array( $value ) && $value ) {
			foreach ( $value as $v ) {
				if ( ! is_int( $v ) && ! is_string( $v ) && ! is_float( $v ) && ! is_bool( $v ) && ! $v instanceof DateTimeInterface ) {
					throw new InvalidArgumentException( 'Offset of value should be an integer, string, float, boolean or DateTimeInterface.' );
				}
			}
		}

		return is_array( $value ) ? $value : [ $value ];
	}
}
