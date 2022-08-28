<?php
/**
 * @license GPL-2.0
 *
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Schema\StellarWP\DB\QueryBuilder\Types;

/**
 * @since 1.0.0
 */
class JoinType extends Type {
    const INNER = 'INNER';
    const LEFT = 'LEFT';
    const RIGHT = 'RIGHT';
}
