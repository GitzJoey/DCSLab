import Pusher from 'pusher-js';

const install = app => {
  window.Pusher = Pusher;
}

export { install as default }
