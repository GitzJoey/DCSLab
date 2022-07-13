import Pusher from 'pusher-js';

const install = app => {

  const pusherConfig = {
      appId: import.meta.env.VITE_PUSHER_APP_ID,
      key: import.meta.env.VITE_PUSHER_APP_KEY,
      secret: import.meta.env.VITE_PUSHER_APP_SECRET,
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER
  };

  window.Pusher = new Pusher(pusherConfig);
}

export { install as default }
