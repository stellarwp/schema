<?php
/**
 * @license MIT
 *
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

if (\PHP_VERSION_ID < 80000 && !interface_exists('StellarWP__SchemaStringable')) {
    interface StellarWP__SchemaStringable
    {
        /**
         * @return string
         */
        public function __toString();
    }
}
