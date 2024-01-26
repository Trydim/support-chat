export const DEBUG = !true;

export const MAIN_URL = location.host.includes('support') ? '/' : 'https://vistegra.by/support/',
             SUPPORT_KEY = 'support-user-key',
             POSITION = ['topLeft', 'topRight', 'bottomLeft', 'bottomRight'];

export const SYNC_DELAY = 3600000, // 1h
             SYNC_INTERVAL = 15000; // 15s
