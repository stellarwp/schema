<?php

namespace StellarWP\Schema\Tests;

use StellarWP\ContainerContract\ContainerInterface;

class Container implements ContainerInterface {
	protected $container;

	public function __construct() {
		$this->container = new \lucatume\DI52\Container();
	}

	public function bind( string $id, $implementation = null ) {
		return $this->container->bind( $id, $implementation );
	}

	public function get( string $id ) {
		return $this->container->get( $id );
	}

	public function has( string $id ) {
		return $this->container->has( $id );
	}

	public function singleton( string $id, $implementation = null ) {
		return $this->container->singleton( $id, $implementation );
	}

	public function __call( $name, $args ) {
		return $this->container->{$name}( ...$args );
	}
}
