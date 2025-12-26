<?php

namespace StellarWP\Schema\Tests\Traits;

use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

/**
 * Trait for capturing and asserting query snapshots.
 *
 * @since 3.2.0
 */
trait Query_Snapshot_Assertions {
	use SnapshotAssertions;

	/**
	 * The captured query from the paginate filter.
	 *
	 * @var string
	 */
	protected $captured_query = '';

	/**
	 * Sets up the query capture filter.
	 *
	 * @before
	 *
	 * @return void
	 */
	protected function set_up_query_capture(): void {
		$this->captured_query = '';

		add_filter( 'stellarwp_schema_custom_table_paginate_query', function( $query ) {
			$this->captured_query = $query;
			return $query;
		}, 10, 1 );

		add_filter( 'stellarwp_schema_custom_table_total_items_query', function( $query ) {
			$this->captured_query = $query;
			return $query;
		}, 10, 1 );
	}

	/**
	 * Tears down the query capture filter.
	 *
	 * @after
	 *
	 * @return void
	 */
	protected function tear_down_query_capture(): void {
		$this->captured_query = '';

		remove_all_filters( 'stellarwp_schema_custom_table_paginate_query' );
		remove_all_filters( 'stellarwp_schema_custom_table_total_items_query' );
	}

	/**
	 * Gets the captured query.
	 *
	 * @return string
	 */
	protected function get_captured_query(): string {
		return $this->captured_query;
	}

	/**
	 * Asserts that the captured query matches a stored snapshot.
	 *
	 * @return void
	 */
	protected function assertCapturedQueryMatchesSnapshot(): void {
		$this->assertNotEmpty( $this->captured_query );
		$this->assertMatchesCodeSnapshot( $this->captured_query );
	}

	/**
	 * Asserts that the captured query contains the expected pattern.
	 *
	 * @param string $expected_pattern The expected pattern.
	 * @param string $message          Optional message.
	 *
	 * @return void
	 */
	protected function assertQueryContains( string $expected_pattern, string $message = '' ): void {
		$this->assertStringContainsString(
			$expected_pattern,
			$this->captured_query,
			$message ?: 'Query does not contain expected pattern'
		);
	}
}
