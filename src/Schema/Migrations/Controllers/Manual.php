<?php

namespace StellarWP\Migrations\Controllers;

use Exception;
use lucatume\DI52\App;
use lucatume\DI52\Container;
use StellarWP\Schema\Schema;
use StellarWP\Migrations;
use StellarWP\Migrations\Actions\ClearCompletedUpgrade;
use StellarWP\Migrations\Actions\ManuallyRunMigration;
use StellarWP\Migrations\Contracts\Migration;

/**
 * Class Manual
 *
 * Handles and admin request to manually trigger migrations
 *
 * @since 2.9.2
 */
class Manual {
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var Migrations\Collection
	 */
	protected $migrations;

	/**
	 * Constructor.
	 *
	 * @since 2.9.2
	 *
	 * @param Container|null $container The DI container.
	 */
	public function __construct( $container = null ) {
		$this->container = $container ?: App::container();

		$this->migrations = $this->container->make( Schema::class )::migrations();
	}

	/**
	 * @since 2.9.2
	 */
	public function __invoke() {
		if ( ! empty( $_GET['stellar-run-migration'] ) ) {
			$migration_to_run = $_GET['stellar-run-migration'];
		}

		if ( ! empty( $_GET['stellar-clear-update'] ) ) {
			$migration_to_clear = $_GET['stellar-clear-update'];
		}

		$has_migration = isset( $migration_to_run ) || isset( $migration_to_clear );

		if ( $has_migration && ! current_user_can( 'manage_options' ) ) {
			$id = 'invalid-migration-permissions';
			$description = __( 'You do not have the permission to manually run or clear migrations.', 'stellarwp-schema' );

			/**
			 * Allow for hooking to the invalid permissions message.
			 *
			 * @param string $id The id of the message.
			 * @param string $description The description of the message.
			 */
			do_action( 'stellar_schema_migration_invalid_permissions', $id, $description );

			return;
		}

		if ( isset( $migration_to_run ) ) {
			$this->run_migration( $migration_to_run );
		}

		if ( isset( $migration_to_clear ) ) {
			$this->clear_migration( $migration_to_clear );
		}
	}

	/**
	 * Runs the given automatic migration
	 *
	 * @since 2.9.2
	 *
	 * @param string $migration_id
	 */
	private function run_migration( $migration_id ) {
		if ( ! isset( $this->migrations[ $migration_id ] ) ) {
			$id = 'invalid-migration-id';
			$description = __( "There is no migration with the ID: {$migration_id}", 'stellarwp-schema' );

			/**
			 * Allow for hooking to the invalid id message.
			 *
			 * @param string $id The id of the message.
			 * @param string $description The description of the message.
			 */
			do_action( 'stellar_schema_migration_invalid_id', $id, $description );

			return;
		}

		/** @var Migration $migration */
		$migration = $this->migrations->get( $migration_id );

		/** @var ManuallyRunMigration $manualRunner */
		$manualRunner = give(ManuallyRunMigration::class);

		try {
			$manualRunner( $migration );

			give()->notices->register_notice(
				[
					'id' => 'automatic-migration-run',
					'type' => 'success',
					'description' => "The {$migration_id} migration was manually triggered",
				]
			);
		} catch (Exception $exception) {
			give()->notices->register_notice(
				[
					'id' => 'automatic-migration-run-failure',
					'description' => "The manually triggered {$migration_id} migration ran but failed",
				]
			);
		}
	}

	/**
	 * Clears the manual migration so it may be run again
	 *
	 * @since 2.9.2
	 *
	 * @param string $migrationToClear
	 */
	private function clear_migration( $migrationToClear ) {
		/** @var ClearCompletedUpgrade $clearUpgrade */
		$clearUpgrade = give(ClearCompletedUpgrade::class);

		try {
			$clearUpgrade($migrationToClear);
		} catch (Exception $exception) {
			give()->notices->register_notice(
				[
					'id' => 'clear-migration-failed',
					'description' => "Unable to reset migration. Error: {$exception->getMessage()}",
				]
			);

			return;
		}

		give()->notices->register_notice(
			[
				'id' => 'automatic-migration-cleared',
				'type' => 'success',
				'description' => "The {$migrationToClear} update was cleared and may be run again.",
			]
		);
	}
}
