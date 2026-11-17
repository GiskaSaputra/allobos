import { ethers } from 'ethers';
import { contract, provider, run, wallet } from './common.mjs';

await run(async () => {
    const rpc = provider();
    const signer = wallet(rpc);
    const [network, block, balance, code] = await Promise.all([
        rpc.getNetwork(),
        rpc.getBlockNumber(),
        rpc.getBalance(signer.address),
        rpc.getCode(process.env.CERT_CONTRACT_ADDRESS),
    ]);

    return {
        chain_id: Number(network.chainId),
        block,
        issuer: signer.address,
        balance: ethers.formatEther(balance),
        contract_deployed: code !== '0x',
        issuer_allowed: code !== '0x' ? await contract(rpc).issuers(signer.address) : false,
    };
});
