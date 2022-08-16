<?php

namespace StellarWP\Schema\Tables\Filters;

class Needs_Update_FilterIterator extends \FilterIterator implements \Countable {
	/**
	 * @inheritDoc
	 */
	public function accept(): bool {
		$table = $this->getInnerIterator()->current();

		return ! $table->is_schema_current();
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int {
		return iterator_count( $this->getInnerIterator() );
	}
}
