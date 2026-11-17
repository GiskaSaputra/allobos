import { postJson } from './forms';

// Tanda tangan dompet opsional (EIP-191 lewat personal_sign). Tersembunyi bila peramban tanpa dompet.
export function walletSign(url, message) {
    return {
        available: typeof window.ethereum !== 'undefined',
        busy: false,
        message: '',
        error: false,
        async connect() {
            this.busy = true;
            this.message = '';
            try {
                const [address] = await window.ethereum.request({ method: 'eth_requestAccounts' });
                const hex = `0x${Array.from(new TextEncoder().encode(message)).map((b) => b.toString(16).padStart(2, '0')).join('')}`;
                const signature = await window.ethereum.request({ method: 'personal_sign', params: [hex, address] });
                const result = await postJson(url, { address, signature });
                this.error = false;
                this.message = result.message;
            } catch (e) {
                this.error = true;
                this.message = e.message || 'Dompet menolak permintaan tanda tangan.';
            } finally {
                this.busy = false;
            }
        },
    };
}
