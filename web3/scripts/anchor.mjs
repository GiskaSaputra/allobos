import { contract, provider, run, wallet } from './common.mjs';

// Mencatat sidik digital satu kelas. Sidik yang sudah tercatat (misalnya dari percobaan sebelumnya yang
// terputus) tidak dikirim ulang, tetapi bukti transaksinya diambil dari peristiwa CertificateIssued.
await run(async ({ hashes, classId }) => {
    const rpc = provider();
    const signer = wallet(rpc);
    const cert = contract(signer);

    const fresh = [];
    const existing = {};
    for (const hash of hashes) {
        const record = await cert.verify(hash);
        if (record.issuedAt === 0n) {
            fresh.push(hash);
        } else {
            const [log] = await cert.queryFilter(cert.filters.CertificateIssued(hash), 0, 'latest');
            existing[hash] = log ? { tx_hash: log.transactionHash, block_number: log.blockNumber } : null;
        }
    }

    let tx = null;
    if (fresh.length) {
        const sent = await cert.issueBatch(fresh, classId);
        const receipt = await sent.wait(1, 180000);
        if (!receipt || receipt.status !== 1) throw new Error('Transaksi pencatatan gagal di jaringan');
        tx = { tx_hash: sent.hash, block_number: receipt.blockNumber };
    }

    return {
        transaction: tx,
        existing,
        contract: await cert.getAddress(),
        issuer: signer.address,
    };
});
