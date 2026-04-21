import qz from 'qz-tray';

export async function connectQz() {
    if (!qz.websocket.isActive()) {
        await qz.websocket.connect({ host: '192.168.195.16' });
    }
}

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

    const config = qz.configs.create(printerName);
    await qz.print(config, data);
}
