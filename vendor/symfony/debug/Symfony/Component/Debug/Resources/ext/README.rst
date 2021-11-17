Symfony Debug Extension
=======================
This extension adds a ``symfony_zval_info($key, $array, $options = 0)`` function that:
- exposes zval_hash/refcounts, allowing e.g. efficient exploration of arbitrary structures in PHP,
- does work with references, preventing memory copying.
Its behavior is about the same as:
o enable the extension from source, run:
