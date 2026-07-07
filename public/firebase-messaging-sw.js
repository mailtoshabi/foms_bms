importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyBREoKRNqx2e5PP_c3pjy_hh1SoBh8nujM",
    authDomain: "foms-bms.firebaseapp.com",
    projectId: "foms-bms",
    storageBucket: "foms-bms.firebasestorage.app",
    messagingSenderId: "877225097997",
    appId: "1:877225097997:web:4364f4bc0ad85964788e44"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);

    // ── 1. Try to wake up any open (possibly hidden) app window ──────────────
    //    If the tab is open but backgrounded, postMessage will trigger the
    //    foreground listener which can play the Web Audio buzzer.
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
        windowClients.forEach((client) => {
            client.postMessage({
                type: 'FCM_BUZZER',
                data: payload.data
            });
        });
    });

    // ── 2. Show a system notification ────────────────────────────────────────
    //    Since we send a DATA-ONLY FCM message (no 'notification' field),
    //    this handler always fires — even when the screen is off.
    //    We read title/body from payload.data (embedded by the PHP service).
    const data         = payload.data || {};
    const classHourId  = data.class_hour_id || null;

    // Title and body were placed inside the data payload by FirebaseService.php
    const notificationTitle = data.title || 'Class Join Reminder!';

    const notificationOptions = {
        body: data.body || 'Your teacher is buzzing you to join the class session immediately.',
        icon: '/assets/images/logo.png',
        badge: '/assets/images/logo.png',
        // vibrate works on Android even when screen is off
        vibrate: [400, 150, 400, 150, 400, 150, 600],
        // tag + renotify: each buzzer fires even if one is already on screen
        tag: 'buzzer-' + (classHourId || Date.now()),
        renotify: true,
        // requireInteraction keeps the notification on screen until dismissed
        requireInteraction: true,
        data: {
            url: classHourId
                ? '/student/classes/join/' + classHourId
                : '/student/dashboard',
            class_hour_id: classHourId
        },
        actions: [
            { action: 'join',    title: '📲 Join Class' },
            { action: 'dismiss', title: 'Dismiss'        }
        ]
    };

    return self.registration.showNotification(notificationTitle, notificationOptions);
});

// ── Handle notification click / action button ──────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const data        = event.notification.data || {};
    const classHourId = data.class_hour_id || null;

    // If "Dismiss" action button tapped — just close, no page open
    if (event.action === 'dismiss') {
        return;
    }

    // Build the buzzer-alert URL.
    // This dedicated page auto-plays sound on load because page-open via
    // notification tap counts as a user gesture — bypassing autoplay restrictions.
    const buzzerAlertUrl = classHourId
        ? '/student/classes/buzzer-alert?class_hour_id=' + encodeURIComponent(classHourId)
        : '/student/dashboard';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // If a buzzer-alert page is already open, refresh it with new params
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if (client.url.includes('/buzzer-alert') && 'navigate' in client) {
                    return client.navigate(buzzerAlertUrl).then(c => c && c.focus());
                }
            }
            // Otherwise open a new window with the buzzer-alert page
            if (self.clients.openWindow) {
                return self.clients.openWindow(buzzerAlertUrl);
            }
        })
    );
});
