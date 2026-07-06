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
    
    const notificationTitle = payload.notification.title || 'Class Join Reminder!';
    const notificationOptions = {
        body: payload.notification.body || 'Your teacher is buzzing you to join the class session immediately.',
        icon: '/assets/images/logo.png',
        badge: '/assets/images/logo.png',
        vibrate: [200, 100, 200, 100, 200],
        data: {
            url: payload.data && payload.data.class_hour_id 
                ? '/student/classes/join/' + payload.data.class_hour_id
                : '/student/dashboard'
        }
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click to open page
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const urlToOpen = event.notification.data && event.notification.data.url 
        ? event.notification.data.url 
        : '/student/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if (client.url.includes(urlToOpen) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});
