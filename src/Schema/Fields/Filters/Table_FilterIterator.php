<?php

namespace StellarWP\Schema\Fields\Filters;

class Table_FilterIterator extends \FilterIterator implements \Countable {
	/**
	 * Tables to filter.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string>
	 */
	private $tables = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array<string> $tables Tables to filter.
	 * @param \Iterator $iterator Iterator to filter.
	 */
	public function __construct( $tables, \Iterator $iterator ) {
		parent::__construct( $iterator );

		$this->tables = (array) $tables;
	}

	/**
	 * @inheritDoc
	 */
	public function accept(): bool {
		$field = $this->getInnerIterator()->current();

		return in_array( $field->base_table_name(), $this->tables, true );
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int {
		return iterator_count( $this->getInnerIterator() );
	}
}
