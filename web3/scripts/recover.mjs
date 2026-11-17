import { ethers } from 'ethers';
import { run } from './common.mjs';

// Tanda tangan dompet opsional (EIP-191): mengembalikan alamat penanda tangan pesan.
await run(async ({ message, signature }) => ({ address: ethers.verifyMessage(message, signature) }));
