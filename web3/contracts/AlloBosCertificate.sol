// SPDX-License-Identifier: MIT
pragma solidity ^0.8.24;

/// @title Catatan sidik digital Sertifikat Terverifikasi alloBos
/// @notice Hanya menyimpan sidik digital (SHA-256) dan id kelas tersamarkan. Data pribadi tidak pernah ditulis ke sini.
contract AlloBosCertificate {
    struct Record {
        address issuer;
        uint64 issuedAt;
        bytes32 classId;
        bool revoked;
        string reason;
    }

    address public owner;
    mapping(address => bool) public issuers;
    mapping(bytes32 => Record) private records;

    event CertificateIssued(bytes32 indexed hash, bytes32 indexed classId, address indexed issuer);
    event CertificateRevoked(bytes32 indexed hash, string reason);
    event IssuerUpdated(address indexed issuer, bool allowed);
    event OwnershipTransferred(address indexed previousOwner, address indexed newOwner);

    error NotOwner();
    error NotIssuer();
    error EmptyBatch();
    error AlreadyIssued(bytes32 hash);
    error NotIssued(bytes32 hash);
    error AlreadyRevoked(bytes32 hash);
    error ZeroAddress();

    modifier onlyOwner() {
        if (msg.sender != owner) revert NotOwner();
        _;
    }

    modifier onlyIssuer() {
        if (!issuers[msg.sender]) revert NotIssuer();
        _;
    }

    constructor(address firstIssuer) {
        if (firstIssuer == address(0)) revert ZeroAddress();
        owner = msg.sender;
        issuers[firstIssuer] = true;
        emit OwnershipTransferred(address(0), msg.sender);
        emit IssuerUpdated(firstIssuer, true);
    }

    function setIssuer(address issuer, bool allowed) external onlyOwner {
        if (issuer == address(0)) revert ZeroAddress();
        issuers[issuer] = allowed;
        emit IssuerUpdated(issuer, allowed);
    }

    function transferOwnership(address newOwner) external onlyOwner {
        if (newOwner == address(0)) revert ZeroAddress();
        emit OwnershipTransferred(owner, newOwner);
        owner = newOwner;
    }

    /// @notice Mencatat seluruh sertifikat satu kelas dalam satu transaksi.
    function issueBatch(bytes32[] calldata hashes, bytes32 classId) external onlyIssuer {
        if (hashes.length == 0) revert EmptyBatch();
        uint64 nowTs = uint64(block.timestamp);
        for (uint256 i = 0; i < hashes.length; i++) {
            bytes32 hash = hashes[i];
            if (records[hash].issuedAt != 0) revert AlreadyIssued(hash);
            records[hash] = Record(msg.sender, nowTs, classId, false, "");
            emit CertificateIssued(hash, classId, msg.sender);
        }
    }

    function revoke(bytes32 hash, string calldata reason) external onlyIssuer {
        Record storage record = records[hash];
        if (record.issuedAt == 0) revert NotIssued(hash);
        if (record.revoked) revert AlreadyRevoked(hash);
        record.revoked = true;
        record.reason = reason;
        emit CertificateRevoked(hash, reason);
    }

    function verify(bytes32 hash) external view returns (address issuer, uint64 issuedAt, bytes32 classId, bool revoked, string memory reason) {
        Record storage record = records[hash];
        return (record.issuer, record.issuedAt, record.classId, record.revoked, record.reason);
    }
}
