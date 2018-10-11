// ATTENTION:
// METTRE CE FICHIER A LA RACINE DU NOM DE DOMAINE

// If you do not serve/host your project using Firebase Hosting see https://firebase.google.com/docs/web/setup

 // [START initialize_firebase_in_sw]
 // Give the service worker access to Firebase Messaging.
 // Note that you can only use Firebase Messaging here, other Firebase libraries
 // are not available in the service worker.
 importScripts('https://www.gstatic.com/firebasejs/5.4.1/firebase-app.js');
 importScripts('https://www.gstatic.com/firebasejs/5.4.1/firebase-messaging.js');

 // Initialize the Firebase app in the service worker by passing in the
 // messagingSenderId.
 firebase.initializeApp({
   'messagingSenderId': '714773471916' // 'YOUR-SENDER-ID'
 });

 // Retrieve an instance of Firebase Messaging so that it can handle background
 // messages.
 const messaging = firebase.messaging();
 

// If you would like to customize notifications that are received in the
// background (Web app is closed or not in browser focus) then you should
// implement this optional method.
// [START background_handler]
messaging.setBackgroundMessageHandler(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
  var notificationTitle = 'Background Message Title';
  var notificationOptions = {
    body: 'Background Message body.',
    icon: 'firebase-logo.png'
  };

  return self.registration.showNotification(notificationTitle,
    notificationOptions);
});
// [END background_handler]