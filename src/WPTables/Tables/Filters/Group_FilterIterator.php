<?php

namespace StellarWP\WPTables\Tables\Filters;

class Group_FilterIterator extends \FilterIterator {
	/**
	 * Groups to filter.
	 *
	 * @since 1.0.0
	 *
	 * @var array<string>
	 */
	private $groups = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string> $groups Paths to filter.
	 * @param \Iterator $iterator Iterator to filter.
	 */
	public function __construct( array $groups, \Iterator $iterator ) {
		parent::__construct( $iterator );

		$this->groups = (array) $groups;
	}

	/**
	 * @inheritDoc
	 */
	public function accept(): bool {
		$table = $this->getInnerIterator()->current();

		return in_array( $table->get_group(), $this->groups, true );
	}
}
