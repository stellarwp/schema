# Future plans

## WPCLI

It'd be rad to have some sort of auto-namespaced WPCLI commands for schema management

Here are some braindump notes:

```
wp {namespace}:schema help (list the tables)
wp {namespace}:schema {table} {up|down|drop|version}

wp tec:schema tec_events up

// Maybe build the namespace from the base namespace of the plugin that is consuming it.
$prefix = str_replace( '\\', '-', __NAMESPACE__ );

apply_filters( 'stellarwp_schema_wpcli_namespace', $prefix, __NAMESPACE__ );

wp tribe-pue:schema
```
