<?php

namespace StellarWP\Schema\Migrations;

use Exception;
use lucatume\DI52\App;
use lucatume\DI52\Container;
use StellarWP\Schema\Migrations\Contracts\Migration;
use StellarWP\Schema\Schema;
use StellarWP\Schema\StellarWP\DB\Database\Exceptions\DatabaseQueryException;

/**
 * Class Runner
 *
 * @since 2.9.0
 */
class Runner {
	/**
	 * List of completed migrations.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	private $completed_migrations;

	/**
	 * @since 2.9.0
	 *
	 * @var Collection
	 */
	private $migrations;

	/**
	 * @since 2.9.0
	 *
	 * @var Container
	 */
	private $container;

	/**
	 *  MigrationsRunner constructor.
	 *
	 * @param MigrationsRegister	 $migrationRegister
	 * @param MigrationLogFactory	$migrationLogFactory
	 * @param MigrationLogRepository $migrationLogRepository
	 */
	public function __construct( $container = null ) {
		$this->container  = $container ?: App::container();
		$this->migrations = $this->container->make( Schema::class )::migrations();

		$this->completed_migrations = $this->migrationLogRepository->getCompletedMigrationsIDs();
	}

	/**
	 * Run database migrations.
	 *
	 * @since 2.9.0
	 */
	public function run() {
		global $wpdb;

		if ( ! $this->has_migration_to_run() ) {
			return;
		}

		// Stop Migration Runner if there are failed migrations
		if ( $this->migrationLogRepository->getFailedMigrationsCountByIds(
			$this->migrationRegister->getRegisteredIds()
		) ) {
			return;
		}

		// Store and sort migrations by timestamp
		$migrations = [];

		foreach ( $this->migrationRegister->getMigrations() as $migrationClass ) {
			/* @var Migration $migrationClass */
			$migrations[ $migrationClass::timestamp() . '_' . $migrationClass::id() ] = $migrationClass;
		}

		ksort( $migrations );

		foreach ( $migrations as $key => $migrationClass ) {
			$migrationId = $migrationClass::id();

			if ( in_array( $migrationId, $this->completed_migrations, true ) ) {
				continue;
			}

			$migrationLog = $this->migrationLogFactory->make( $migrationId );

			// Begin transaction
			$wpdb->query( 'START TRANSACTION' );

			try {
				/** @var Migration $migration */
				$migration = give( $migrationClass );

				$migration->run();

				// Save migration status
				$migrationLog->setStatus( MigrationLogStatus::SUCCESS );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK' );

				$migrationLog->setStatus( MigrationLogStatus::FAILED );
				$migrationLog->setError( $exception );

				give()->notices->register_notice(
					[
						'id' => 'migration-failure',
						'description' => sprintf(
							'%1$s <a href="https://givewp.com/support/">https://givewp.com/support</a>',
							esc_html__(
								'There was a problem running the migrations. Please reach out to GiveWP support for assistance:',
								'give'
							)
						),
					]
				);
			}

			try {
				$migrationLog->save();
			} catch ( DatabaseQueryException $e ) {
				Log::error(
					'Failed to save migration log',
					[
						'Error Message' => $e->getMessage(),
						'Query Errors' => $e->getQueryErrors(),
					]
				);
			}

			// Stop Migration Runner if migration has failed
			if ( $migrationLog->getStatus() === MigrationLogStatus::FAILED ) {
				break;
			}

			// Commit transaction if successful
			$wpdb->query( 'COMMIT' );
		}
	}

	/**
	 * Return whether or not all migrations completed.
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function has_migration_to_run() {
		return (bool) array_diff( $this->migrationRegister->getRegisteredIds(), $this->completed_migrations );
	}
}
