<?php

namespace StellarWP\Schema\Tables\Filters;

class Needs_Update_FilterIterator extends \FilterIterator {
	/**
	 * @inheritDoc
	 */
	public function accept(): bool {
		$table = $this->getInnerIterator()->current();

		return ! $table->is_schema_current();
	}
}
