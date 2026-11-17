<?php

$name = env('CHAIN_NAME', 'Polygon Amoy');

return [
    'name' => $name,
    'id' => (int) env('CHAIN_ID', 80002),
    'rpc_url' => env('CHAIN_RPC_URL'),
    'explorer_url' => rtrim((string) env('CHAIN_EXPLORER_URL', ''), '/'),
    'contract' => env('CERT_CONTRACT_ADDRESS'),
    'issuer_key' => env('ISSUER_PRIVATE_KEY'),
    'salt' => env('CERT_HASH_SALT', ''),
    'testnet' => (bool) env('CHAIN_TESTNET', true),
    'min_balance' => (float) env('CHAIN_MIN_BALANCE', 0.05),
    'node' => env('NODE_BINARY', 'node'),
    'scripts' => base_path('web3/scripts'),
    'timeout' => 200,
];
