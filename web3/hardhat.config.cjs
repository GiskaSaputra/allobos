require('@nomicfoundation/hardhat-ethers');
require('@nomicfoundation/hardhat-chai-matchers');

const key = process.env.ISSUER_PRIVATE_KEY;

/** @type {import('hardhat/config').HardhatUserConfig} */
module.exports = {
    solidity: {
        version: '0.8.24',
        settings: { optimizer: { enabled: true, runs: 200 } },
    },
    networks: {
        localhost: { url: 'http://127.0.0.1:8545' },
        amoy: {
            url: process.env.CHAIN_RPC_URL || 'https://rpc-amoy.polygon.technology',
            chainId: 80002,
            accounts: key ? [key] : [],
        },
    },
};
