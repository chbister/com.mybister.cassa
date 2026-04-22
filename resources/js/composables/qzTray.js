import qz from 'qz-tray';
import { ref } from 'vue';

export const isConnected = ref(false);

export async function connectQz() {
    if (!qz.websocket.isActive()) {
        try {
            //await qz.websocket.connect({ host: '192.168.195.16' });
            await qz.websocket.connect();
            isConnected.value = true;
        } catch (e) {
            isConnected.value = false;
            throw e;
        }
    } else {
        isConnected.value = true;
    }
}

qz.websocket.setClosedCallbacks(() => {
    isConnected.value = false;
});

qz.websocket.setErrorCallbacks(() => {
    isConnected.value = false;
});

export async function disconnectQz() {
    if (qz.websocket.isActive()) {
        await qz.websocket.disconnect();
    }
}

export async function getDefaultPrinter() {
    await connectQz();
    return await qz.printers.getDefault();
}

export async function printRaw(printerName, data) {
    await connectQz();

    const config = qz.configs.create(printerName, {
        encoding: 'Cp858',
    });
    await qz.print(config, data);
}

qz.security.setCertificatePromise((resolve, reject) => {
    fetch('/qz/certificate')
        .then((res) => res.text())
        .then(resolve)
        .catch(reject);
});

qz.security.setSignatureAlgorithm('SHA512');

qz.security.setSignaturePromise((toSign) => {
    return (resolve, reject) => {
        fetch('/qz/sign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ data: toSign }),
        })
            .then((res) => res.text())
            .then(resolve)
            .catch(reject);
    };
});
