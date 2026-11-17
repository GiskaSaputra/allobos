// Deploy kontrak. Dompet penerbit pertama adalah akun yang men-deploy.
// Pakai: npx hardhat run scripts/deploy.cjs --network amoy
const fs = require('node:fs');
const path = require('node:path');
const hre = require('hardhat');

async function main() {
    const [deployer] = await hre.ethers.getSigners();
    const factory = await hre.ethers.getContractFactory('AlloBosCertificate');
    const contract = await factory.deploy(deployer.address);
    await contract.waitForDeployment();

    const address = await contract.getAddress();
    const network = hre.network.name;
    const record = {
        network,
        chainId: Number((await hre.ethers.provider.getNetwork()).chainId),
        address,
        deployer: deployer.address,
        transaction: contract.deploymentTransaction()?.hash ?? null,
        deployedAt: new Date().toISOString(),
    };

    const dir = path.join(__dirname, '..', 'deployments');
    fs.mkdirSync(dir, { recursive: true });
    fs.writeFileSync(path.join(dir, `${network}.json`), `${JSON.stringify(record, null, 2)}\n`);

    process.stdout.write(`AlloBosCertificate di ${network}: ${address}\nIsi CERT_CONTRACT_ADDRESS=${address}\n`);
}

main().catch((error) => {
    process.stderr.write(`${error.message}\n`);
    process.exitCode = 1;
});
