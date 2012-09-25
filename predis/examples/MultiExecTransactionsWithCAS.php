<?php
require_once 'SharedConfigurations.php';

/*
This is an implementation of an atomic client-side ZPOP using the support for 
check-and-set (CAS) operations with MULTI/EXEC transactions, as described in 
"WATCH explained" from http://redis.io/topics/transactions

First, populate your database with a tiny sample data set:

./redis-cli
SELECT 15
ZADD zset 1 a
ZADD zset 2 b
ZADD zset 3 c
*/

function zpop($client, $zsetKey) {
    $element = null;
    $options = array(
        'cas'   => true,     // Initialize with support for CAS operations
        'watch' => $zsetKey, // Key that needs to be WATCHed to detect changes
    );

    $tx = $client->multiExec($options);
    @list($element) = $tx->zrange($zsetKey, 0, 0);
    if (isset($element)) {
        $tx->multi();     // With CAS, MULTI *must* be explicitly invoked.
        $tx->zrem($zsetKey, $element);
        $tx->exec();
    }
    return $element;
}

$redis = new Predis_Client($single_server, 'dev');
$zpopped = zpop($redis, 'zset');
echo isset($zpopped) ? "ZPOPed $zpopped" : "Nothing to ZPOP!", "\n";
?>