<?php

namespace StellarWP\Schema;

use StellarWP\Schema\Builder;
use StellarWP\Schema\Fields;
use StellarWP\Schema\Tables;

/**
 * A helper class for registering StellarWP Schema resources.
 */
class Register {
	/**
	 * Register a field schema.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field Field class.
	 *
	 * @return Fields\Field_Schema_Interface
	 */
	public static function field( $field ) {
		if ( is_string( $field ) ) {
			$field = new $field();
		}

		$container = Container::init();

		$container->make( Fields\Collection::class )->add( $field );

		// If we've already executed plugins_loaded, automatically add the field.
		if ( did_action( 'plugins_loaded' ) ) {
			$container->make( Builder::class )->up();
		}

		return $field;
	}

	/**
	 * Removes a field from the register.
	 *
	 * @since 1.0.0
	 *
	 * @param string|Fields\Field_Schema_Interface $field Field Schema class.
	 *
	 * @return Fields\Field_Schema_Interface
	 */
	public static function remove_field( $field ) {
		if ( is_string( $field ) ) {
			$field = new $field();
		}

		$container = Container::init();

		// If we've already executed plugins_loaded, automatically remove the field.
		if ( did_action( 'plugins_loaded' ) ) {
			$field->drop();
		}

		$container->make( Fields\Collection::class )->remove( $field::get_schema_slug() );

		return $field;
	}

	/**
	 * Removes a table from the register.
	 *
	 * @since 1.0.0
	 *
	 * @param string|Tables\Table_Schema_Interface $table Table Schema class.
	 *
	 * @return Tables\Table_Schema_Interface
	 */
	public static function remove_table( $table ) {
		if ( is_string( $table ) ) {
			$table = new $table();
		}

		$container = Container::init();

		// If we've already executed plugins_loaded, automatically remove the field.
		if ( did_action( 'plugins_loaded' ) ) {
			$table->drop();
		}

		$container->make( Tables\Collection::class )->remove( $table::base_table_name() );

		return $table;
	}

	/**
	 * Register a table schema.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table Table class.
	 *
	 * @return Tables\Table_Schema_Interface
	 */
	public static function table( $table ) {
		if ( is_string( $table ) ) {
			$table = new $table();
		}

		$container = Container::init();

		$container->make( Tables\Collection::class )->add( $table );

		// If we've already executed plugins_loaded, automatically add the table.
		if ( did_action( 'plugins_loaded' ) ) {
			$container->make( Builder::class )->up();
		}

		return $table;
	}
}
