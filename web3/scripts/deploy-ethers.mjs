// Deploy kontrak tanpa Hardhat, untuk server yang hanya punya Node dan ethers.
// Pakai: CHAIN_RPC_URL=... ISSUER_PRIVATE_KEY=... node scripts/deploy-ethers.mjs <artefak AlloBosCertificate.json>
// Artefak (abi dan bytecode) dihasilkan `npx hardhat compile` di mesin pengembang.
import fs from 'node:fs';
import { ethers } from 'ethers';
import { provider, wallet } from './common.mjs';

const artifactPath = process.argv[2];
if (!artifactPath) {
    process.stderr.write('Sebutkan berkas artefak AlloBosCertificate.json\n');
    process.exit(1);
}

const { abi, bytecode } = JSON.parse(fs.readFileSync(artifactPath, 'utf8'));
const signer = wallet(provider());
const balance = await signer.provider.getBalance(signer.address);
if (balance === 0n) {
    process.stderr.write(`Saldo ${signer.address} masih 0. Isi POL uji dari faucet dulu.\n`);
    process.exit(1);
}

const factory = new ethers.ContractFactory(abi, bytecode, signer);
const contract = await factory.deploy(signer.address);
await contract.waitForDeployment();

process.stdout.write(JSON.stringify({
    address: await contract.getAddress(),
    deployer: signer.address,
    transaction: contract.deploymentTransaction()?.hash ?? null,
    balance_before: ethers.formatEther(balance),
}));
