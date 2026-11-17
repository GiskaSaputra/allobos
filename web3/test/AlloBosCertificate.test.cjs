const { expect } = require('chai');
const { ethers } = require('hardhat');

const h = (text) => ethers.sha256(ethers.toUtf8Bytes(text));

describe('AlloBosCertificate', () => {
    async function deploy() {
        const [owner, issuer, stranger, newIssuer] = await ethers.getSigners();
        const contract = await (await ethers.getContractFactory('AlloBosCertificate')).deploy(issuer.address);
        return { contract, owner, issuer, stranger, newIssuer };
    }

    it('mencatat satu kelas dalam satu transaksi dan bisa diverifikasi', async () => {
        const { contract, issuer } = await deploy();
        const hashes = [h('ALB-2026-AAAAAA'), h('ALB-2026-BBBBBB')];
        const classId = h('program:1');

        await expect(contract.connect(issuer).issueBatch(hashes, classId))
            .to.emit(contract, 'CertificateIssued').withArgs(hashes[0], classId, issuer.address);

        const record = await contract.verify(hashes[1]);
        expect(record.issuer).to.equal(issuer.address);
        expect(record.issuedAt).to.be.greaterThan(0n);
        expect(record.classId).to.equal(classId);
        expect(record.revoked).to.equal(false);
    });

    it('menolak penerbit yang tidak terdaftar', async () => {
        const { contract, stranger } = await deploy();
        await expect(contract.connect(stranger).issueBatch([h('x')], h('c'))).to.be.revertedWithCustomError(contract, 'NotIssuer');
        await expect(contract.connect(stranger).revoke(h('x'), 'tidak')).to.be.revertedWithCustomError(contract, 'NotIssuer');
    });

    it('menolak sidik digital ganda dan batch kosong', async () => {
        const { contract, issuer } = await deploy();
        await contract.connect(issuer).issueBatch([h('a')], h('c'));
        await expect(contract.connect(issuer).issueBatch([h('a')], h('c'))).to.be.revertedWithCustomError(contract, 'AlreadyIssued');
        await expect(contract.connect(issuer).issueBatch([], h('c'))).to.be.revertedWithCustomError(contract, 'EmptyBatch');
    });

    it('mencabut sertifikat dengan alasan dan menolak cabut ganda', async () => {
        const { contract, issuer } = await deploy();
        await contract.connect(issuer).issueBatch([h('a')], h('c'));

        await expect(contract.connect(issuer).revoke(h('a'), 'Nama keliru')).to.emit(contract, 'CertificateRevoked').withArgs(h('a'), 'Nama keliru');
        const record = await contract.verify(h('a'));
        expect(record.revoked).to.equal(true);
        expect(record.reason).to.equal('Nama keliru');

        await expect(contract.connect(issuer).revoke(h('a'), 'lagi')).to.be.revertedWithCustomError(contract, 'AlreadyRevoked');
        await expect(contract.connect(issuer).revoke(h('b'), 'tidak ada')).to.be.revertedWithCustomError(contract, 'NotIssued');
    });

    it('hanya pemilik yang bisa mengganti penerbit', async () => {
        const { contract, owner, issuer, stranger, newIssuer } = await deploy();
        await expect(contract.connect(stranger).setIssuer(newIssuer.address, true)).to.be.revertedWithCustomError(contract, 'NotOwner');

        await contract.connect(owner).setIssuer(issuer.address, false);
        await contract.connect(owner).setIssuer(newIssuer.address, true);
        await expect(contract.connect(issuer).issueBatch([h('a')], h('c'))).to.be.revertedWithCustomError(contract, 'NotIssuer');
        await contract.connect(newIssuer).issueBatch([h('a')], h('c'));
        expect((await contract.verify(h('a'))).issuer).to.equal(newIssuer.address);
    });

    it('sertifikat yang tidak ada mengembalikan catatan kosong', async () => {
        const { contract } = await deploy();
        const record = await contract.verify(h('tidak-ada'));
        expect(record.issuedAt).to.equal(0n);
        expect(record.issuer).to.equal(ethers.ZeroAddress);
    });
});
