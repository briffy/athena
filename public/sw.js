self.addEventListener('activate', async () => {
    // This will be called only once when the service worker is activated.
  });

  self.addEventListener('fetch', async () => {

  });

  self.addEventListener('message', function (event) {
    
    
    
    if(event.data.type === 'notification')
    {
      self.registration.getNotifications().then(function(notifications) {
        var new_message = null;

        notifications.forEach(function(current) {
          if(current.title === event.data.user)
          {
             new_message = current.body+"\n"+event.data.message;
             current.close();
          }
        });

        if(new_message === null)
        {
          var new_message = event.data.message;
        }
        
        self.registration.showNotification(event.data.user, 
          { 
            body: new_message,
            badge: '/images/notification.png',
            silent: true
          });
      });
      
    }
  });
