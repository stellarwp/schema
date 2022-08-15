<?php

namespace StellarWP\Schema\Tests;

use StellarWP\Schema\Builder\Abstract_Custom_Table;

class ChangedCustomTable extends Abstract_Custom_Table {
	/**
	 * {@inheritdoc}
	 */
	const SCHEMA_VERSION_OPTION = 'simple_table';

	/**
	 * {@inheritdoc}
	 */
	const SCHEMA_VERSION = '2.0.0';

	/**
	 * {@inheritdoc}
	 */
	protected static $base_table_name = 'simple_table';

	/**
	 * {@inheritdoc}
	 */
	protected static $group = 'schema_tests';

	/**
	 * {@inheritdoc}
	 */
	protected static $uid_column = 'id';

	/**
	 * {@inheritdoc}
	 */
	protected function get_update_sql() {
		global $wpdb;
		$table_name = self::table_name( true );
		$charset_collate = $wpdb->get_charset_collate();

		return "
			CREATE TABLE `{$table_name}` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(64) NOT NULL,
				`slug` varchar(64) NOT NULL,
				`something` varchar(64) NOT NULL,
				PRIMARY KEY (`id`),
				KEY `slug` (`slug`)
				KEY `something` (`something`)
			) {$charset_collate};
		";
	}
}
