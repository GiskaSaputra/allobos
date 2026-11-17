import { contract, provider, run, wallet } from './common.mjs';

await run(async ({ hash, reason }) => {
    const cert = contract(wallet(provider()));
    const record = await cert.verify(hash);
    if (record.revoked) return { already: true };

    const sent = await cert.revoke(hash, reason.slice(0, 120));
    const receipt = await sent.wait(1, 180000);
    if (!receipt || receipt.status !== 1) throw new Error('Transaksi pencabutan gagal di jaringan');

    return { tx_hash: sent.hash, block_number: receipt.blockNumber };
});
