const DB_NAME = 'success-mandiri-offline';
const DB_VERSION = 1;
const DEFAULT_API_BASE_URL = 'https://successmandiri.com';

const endpointMap = {
    'transaksi-do': '/api/transaksi-do',
    operasional: '/api/operasional',
    'tambah-saldo': '/api/tambah-saldo',
};

const refEndpoints = {
    penjual: '/api/penjual?per_page=100',
    supir: '/api/supir?per_page=100',
    kendaraan: '/api/kendaraan?per_page=100',
    pekerja: '/api/pekerja?per_page=100',
};

let db;
let toastTimer;

const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => Array.from(document.querySelectorAll(selector));

document.addEventListener('DOMContentLoaded', async () => {
    db = await openDatabase();
    initDefaults();
    bindEvents();
    updateNetworkBadge();
    await hydrateAuthStatus();
    await renderReferences();
    await renderQueue();
});

window.addEventListener('online', () => {
    updateNetworkBadge();
    syncQueue();
});

window.addEventListener('offline', updateNetworkBadge);

function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = () => {
            const database = request.result;

            if (!database.objectStoreNames.contains('queue')) {
                const queue = database.createObjectStore('queue', { keyPath: 'client_uuid' });
                queue.createIndex('sync_status', 'sync_status');
                queue.createIndex('entity_type', 'entity_type');
                queue.createIndex('created_at', 'created_at');
            }

            if (!database.objectStoreNames.contains('refs')) {
                database.createObjectStore('refs', { keyPath: 'key' });
            }
        };

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function initDefaults() {
    $('#apiBaseUrl').value = localStorage.getItem('apiBaseUrl') || DEFAULT_API_BASE_URL;
    setDefaultDateTimes();
}

function bindEvents() {
    $('#saveSettingsButton').addEventListener('click', saveApiBaseUrl);
    $('#loginButton').addEventListener('click', login);
    $('#logoutButton').addEventListener('click', logout);
    $('#refreshRefsButton').addEventListener('click', refreshReferences);
    $('#syncButton').addEventListener('click', syncQueue);

    $$('.tab').forEach((button) => {
        button.addEventListener('click', () => activateTab(button.dataset.tab));
    });

    $('#doForm').addEventListener('submit', (event) => handleFormSubmit(event, 'transaksi-do'));
    $('#operasionalForm').addEventListener('submit', (event) => handleFormSubmit(event, 'operasional'));
    $('#saldoForm').addEventListener('submit', (event) => handleFormSubmit(event, 'tambah-saldo'));
}

function activateTab(tabId) {
    $$('.tab').forEach((button) => button.classList.toggle('active', button.dataset.tab === tabId));
    $$('.tab-panel').forEach((panel) => panel.classList.toggle('active', panel.id === tabId));
}

function updateNetworkBadge() {
    const badge = $('#networkBadge');
    badge.textContent = navigator.onLine ? 'Online' : 'Offline';
    badge.classList.toggle('online', navigator.onLine);
}

function saveApiBaseUrl() {
    const url = normalizeBaseUrl($('#apiBaseUrl').value);
    localStorage.setItem('apiBaseUrl', url);
    $('#apiBaseUrl').value = url;
    showToast('URL API disimpan.');
}

async function login() {
    saveApiBaseUrl();

    const email = $('#email').value.trim();
    const password = $('#password').value;

    if (!email || !password) {
        showToast('Email dan password wajib diisi.');
        return;
    }

    try {
        const response = await fetch(apiUrl('/api/login'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ email, password, device_name: 'android-offline' }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || firstValidationMessage(data) || 'Login gagal.');
        }

        localStorage.setItem('apiToken', data.token);
        localStorage.setItem('userName', data.user?.name || email);
        $('#password').value = '';
        await hydrateAuthStatus();
        await refreshReferences();
        showToast('Login berhasil.');
    } catch (error) {
        showToast(error.message);
    }
}

async function logout() {
    localStorage.removeItem('apiToken');
    localStorage.removeItem('userName');
    await hydrateAuthStatus();
    showToast('Token lokal dihapus.');
}

async function hydrateAuthStatus() {
    const token = localStorage.getItem('apiToken');
    const userName = localStorage.getItem('userName');
    $('#authStatus').textContent = token ? `Login sebagai ${userName || 'user'}.` : 'Belum login.';
}

async function refreshReferences() {
    const token = requireToken();
    if (!token) return;

    try {
        for (const [key, endpoint] of Object.entries(refEndpoints)) {
            const response = await authorizedFetch(endpoint);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `Gagal mengambil ${key}.`);
            }

            await putStore('refs', {
                key,
                rows: Array.isArray(data.data) ? data.data : data,
                synced_at: new Date().toISOString(),
            });
        }

        await renderReferences();
        showToast('Data referensi diperbarui.');
    } catch (error) {
        showToast(error.message);
    }
}

async function renderReferences() {
    for (const select of $$('select[data-ref]')) {
        const ref = await getStore('refs', select.dataset.ref);
        const rows = ref?.rows || [];
        select.innerHTML = '<option value="">Pilih data</option>';

        for (const row of rows) {
            const option = document.createElement('option');
            option.value = row.id;
            option.textContent = row.nama || row.name || `#${row.id}`;
            select.appendChild(option);
        }
    }
}

async function handleFormSubmit(event, entityType) {
    event.preventDefault();

    const form = event.currentTarget;
    const payload = formToPayload(form);
    const now = new Date().toISOString();
    const clientUuid = crypto.randomUUID();

    payload.client_uuid = clientUuid;
    payload.client_created_at = now;
    payload.client_updated_at = now;

    await putStore('queue', {
        client_uuid: clientUuid,
        entity_type: entityType,
        endpoint: endpointMap[entityType],
        payload,
        sync_status: 'pending',
        server_id: null,
        last_error: null,
        synced_at: null,
        created_at: now,
        updated_at: now,
    });

    form.reset();
    setDefaultDateTimes();
    await renderQueue();
    showToast('Data disimpan offline.');

    if (navigator.onLine) {
        syncQueue();
    }
}

function formToPayload(form) {
    const formData = new FormData(form);
    const payload = {};

    for (const [key, value] of formData.entries()) {
        if (value === '') continue;

        if (['penjual_id', 'supir_id', 'pihak_id'].includes(key)) {
            payload[key] = Number(value);
        } else if (['tonase', 'harga_satuan', 'upah_bongkar', 'biaya_lain', 'pembayaran_hutang', 'nominal'].includes(key)) {
            payload[key] = Number(value);
        } else if (key === 'tanggal') {
            payload[key] = localDateTimeToIso(value);
        } else {
            payload[key] = value;
        }
    }

    return payload;
}

async function syncQueue() {
    const token = requireToken(false);
    if (!token) {
        showToast('Login dulu sebelum sync.');
        return;
    }

    if (!navigator.onLine) {
        showToast('Masih offline. Data tetap aman di perangkat.');
        return;
    }

    const rows = await getAllStore('queue');
    const candidates = rows
        .filter((row) => ['pending', 'failed'].includes(row.sync_status))
        .sort((a, b) => a.created_at.localeCompare(b.created_at));

    if (candidates.length === 0) {
        showToast('Tidak ada data pending.');
        return;
    }

    for (const row of candidates) {
        await updateQueueRow(row.client_uuid, {
            sync_status: 'syncing',
            updated_at: new Date().toISOString(),
        });
        await renderQueue();

        try {
            const response = await authorizedFetch(row.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(row.payload),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || firstValidationMessage(data) || 'Sync gagal.');
            }

            const serverRecord = data.data || data;
            await updateQueueRow(row.client_uuid, {
                sync_status: 'synced',
                server_id: serverRecord.id || null,
                last_error: null,
                synced_at: new Date().toISOString(),
                updated_at: new Date().toISOString(),
            });
        } catch (error) {
            await updateQueueRow(row.client_uuid, {
                sync_status: 'failed',
                last_error: error.message,
                updated_at: new Date().toISOString(),
            });
        }
    }

    await renderQueue();
    showToast('Sync selesai.');
}

async function renderQueue() {
    const rows = (await getAllStore('queue')).sort((a, b) => b.created_at.localeCompare(a.created_at));
    $('#pendingCount').textContent = rows.filter((row) => row.sync_status === 'pending' || row.sync_status === 'syncing').length;
    $('#failedCount').textContent = rows.filter((row) => row.sync_status === 'failed').length;
    $('#syncedCount').textContent = rows.filter((row) => row.sync_status === 'synced').length;

    const list = $('#queueList');
    list.innerHTML = '';

    for (const row of rows.slice(0, 20)) {
        const item = document.createElement('li');
        item.innerHTML = `
            <strong>${row.entity_type} - ${row.sync_status}</strong>
            <span>${row.client_uuid}</span>
            <span>${row.last_error || row.synced_at || row.created_at}</span>
        `;
        list.appendChild(item);
    }
}

async function updateQueueRow(clientUuid, patch) {
    const row = await getStore('queue', clientUuid);
    await putStore('queue', { ...row, ...patch });
}

function authorizedFetch(endpoint, options = {}) {
    const token = requireToken();

    return fetch(apiUrl(endpoint), {
        ...options,
        headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
            ...(options.headers || {}),
        },
    });
}

function requireToken(showMessage = true) {
    const token = localStorage.getItem('apiToken');

    if (!token && showMessage) {
        showToast('Belum login.');
    }

    return token;
}

function apiUrl(endpoint) {
    return `${normalizeBaseUrl(localStorage.getItem('apiBaseUrl') || DEFAULT_API_BASE_URL)}${endpoint}`;
}

function normalizeBaseUrl(value) {
    return (value || DEFAULT_API_BASE_URL).trim().replace(/\/+$/, '');
}

function localDateTimeToIso(value) {
    return new Date(value).toISOString();
}

function setDefaultDateTimes() {
    const value = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    $$('input[type="datetime-local"]').forEach((input) => {
        if (!input.value) input.value = value;
    });
}

function showToast(message) {
    const toast = $('#toast');
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
}

function firstValidationMessage(data) {
    const first = Object.values(data.errors || {})[0];
    return Array.isArray(first) ? first[0] : null;
}

function requestStore(storeName, mode, callback) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(storeName, mode);
        const store = transaction.objectStore(storeName);
        const request = callback(store);

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function getStore(storeName, key) {
    return requestStore(storeName, 'readonly', (store) => store.get(key));
}

function putStore(storeName, value) {
    return requestStore(storeName, 'readwrite', (store) => store.put(value));
}

function getAllStore(storeName) {
    return requestStore(storeName, 'readonly', (store) => store.getAll());
}
