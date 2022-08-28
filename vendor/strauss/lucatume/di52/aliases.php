<?php
/**
 * Registers the library aliases redirecting calls to the `tad_DI52_`, non-namespaced, class format to the namespaced
 * classes.
 *
 * @license GPL-3.0
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

$aliases = [
    ['StellarWP\Schema\lucatume\DI52\Container', 'tad_DI52_Container'],
    ['StellarWP\Schema\lucatume\DI52\ServiceProvider', 'tad_DI52_ServiceProvider']
];
foreach ($aliases as list($class, $alias)) {
    if (!class_exists($alias)) {
        class_alias($class, $alias);
    }
}
