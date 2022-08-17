<?php
namespace StellarWP\Schema;

use lucatume\DI52\Container as DI52_Container;

class Container extends DI52_Container {
	/**
	 * Container instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Container
	 */
	protected static $instance;

	/**
	 * Initialize the container.
	 *
	 * @since 1.0.0
	 *
	 * @return Container
	 */
	public static function init() {
		if ( null === static::$instance ) {
			static::$instance = new self;
		}

		return static::$instance;
	}
}
