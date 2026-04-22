import qz from 'qz-tray';
import { ref } from 'vue';

export const isConnected = ref(false);
export const printerStatus = ref({
    printerName: null,
    online: null,
    severity: null,
    statusCode: null,
    statusText: null,
    message: null,
    lastEvent: null,
});

export async function connectQz() {
    if (!qz.websocket.isActive()) {
        try {
            await qz.websocket.connect(); // { host: '192.168.195.15' }
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

export async function printerExists(printerName) {
    await connectQz();

    try {
        const foundPrinter = await qz.printers.find(printerName);

        return !!foundPrinter;
    } catch {
        return false;
    }
}

export async function watchPrinterStatus(printerName) {
    await connectQz();

    qz.printers.setPrinterCallbacks((evt) => {
        printerStatus.value = {
            printerName: evt.printerName ?? printerName,
            online: evt.severity !== 'ERROR',
            severity: evt.severity ?? null,
            statusCode: evt.statusCode ?? null,
            statusText: evt.statusText ?? null,
            message: evt.message ?? null,
            lastEvent: evt.eventType ?? null,
        };
    });

    await qz.printers.startListening(printerName);
    await qz.printers.getStatus();
}

export async function stopWatchingPrinterStatus() {
    await qz.printers.stopListening();
}

export async function checkPrinterReady(printerName) {
    await connectQz();

    try {
        const foundPrinter = await qz.printers.find(printerName);

        if (!foundPrinter) {
            return {
                ok: false,
                reason: 'not-found',
                message: 'Drucker wurde von QZ nicht gefunden.',
            };
        }

        await watchPrinterStatus(foundPrinter);

        return {
            ok: true,
            reason: 'listening',
            message: 'Drucker gefunden, Statusabfrage gestartet.',
        };
    } catch (error) {
        return {
            ok: false,
            reason: 'error',
            message: error instanceof Error ? error.message : String(error),
        };
    }
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

