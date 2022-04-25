import Echo from 'laravel-echo';

const install = app => {
    let pusherEcho = new Echo({
        broadcaster: 'pusher',
        key: process.env.MIX_PUSHER_APP_KEY,
        cluster: process.env.MIX_PUSHER_APP_CLUSTER,
        forceTLS: true,
        encryption: true
    });

    let soketiEcho = new Echo({
        broadcaster: 'pusher',
        key: process.env.MIX_PUSHER_APP_KEY,
        wsHost: process.env.MIX_SOKETI_HOST,
        wsPort: process.env.MIX_SOKETI_PORT,
        wssPort: process.env.MIX_SOKETI_PORT,
        forceTLS: false,
        encrypted: false,
        disableStats: true,
        enabledTransports: ['ws'],
    });

    window.Echo = process.env.MIX_BROADCAST_DRIVER === 'pusher' ? pusherEcho : soketiEcho;
}

export { install as default }
