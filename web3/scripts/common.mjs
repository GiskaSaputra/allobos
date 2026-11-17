// Dipanggil dari ChainClient (PHP) lewat proses Node. Masukan JSON lewat stdin, keluaran JSON lewat stdout.
import { ethers } from 'ethers';

export const ABI = [
    'function issueBatch(bytes32[] hashes, bytes32 classId)',
    'function revoke(bytes32 hash, string reason)',
    'function verify(bytes32 hash) view returns (address issuer, uint64 issuedAt, bytes32 classId, bool revoked, string reason)',
    'function issuers(address) view returns (bool)',
    'event CertificateIssued(bytes32 indexed hash, bytes32 indexed classId, address indexed issuer)',
    'event CertificateRevoked(bytes32 indexed hash, string reason)',
];

export async function readInput() {
    let raw = '';
    for await (const chunk of process.stdin) raw += chunk;
    return raw.trim() ? JSON.parse(raw) : {};
}

export function provider() {
    if (!process.env.CHAIN_RPC_URL) throw new Error('CHAIN_RPC_URL belum diatur');
    return new ethers.JsonRpcProvider(process.env.CHAIN_RPC_URL, undefined, { staticNetwork: true });
}

export function contract(runner) {
    if (!process.env.CERT_CONTRACT_ADDRESS) throw new Error('CERT_CONTRACT_ADDRESS belum diatur');
    return new ethers.Contract(process.env.CERT_CONTRACT_ADDRESS, ABI, runner);
}

export function wallet(rpc) {
    if (!process.env.ISSUER_PRIVATE_KEY) throw new Error('ISSUER_PRIVATE_KEY belum diatur');
    return new ethers.Wallet(process.env.ISSUER_PRIVATE_KEY, rpc);
}

export async function run(handler) {
    try {
        const result = await handler(await readInput());
        process.stdout.write(JSON.stringify(result));
    } catch (error) {
        process.stderr.write(error.shortMessage || error.message || String(error));
        process.exitCode = 1;
    }
}
