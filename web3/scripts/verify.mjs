import { contract, provider, run } from './common.mjs';

await run(async ({ hash }) => {
    const record = await contract(provider()).verify(hash);

    return {
        exists: record.issuedAt !== 0n,
        issuer: record.issuer,
        issued_at: Number(record.issuedAt),
        class_id: record.classId,
        revoked: record.revoked,
        reason: record.reason,
    };
});
