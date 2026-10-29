/* Service worker: shows deal-alert push notifications and opens them on click. */
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));

self.addEventListener('push', (event) => {
    let data = {};
    try { data = event.data ? event.data.json() : {}; } catch (e) { data = { title: 'New deal', body: event.data ? event.data.text() : '' }; }

    const options = {
        body: data.body || '',
        icon: data.icon || '/favicon.ico',
        badge: data.icon || '/favicon.ico',
        tag: data.tag || undefined,
        data: { url: data.url || '/' },
    };
    if (data.image) options.image = data.image;

    event.waitUntil(self.registration.showNotification(data.title || 'New deal', options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/';
    event.waitUntil(self.clients.openWindow(url));
});
