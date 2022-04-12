import Echo from 'laravel-echo';

const install = app => {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: process.env.MIX_PUSHER_APP_KEY,
        cluster: process.env.MIX_PUSHER_APP_CLUSTER,
        forceTLS: true
    });
}

export { install as default }
