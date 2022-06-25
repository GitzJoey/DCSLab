import Echo from 'laravel-echo';

const install = app => {
    if (process.env.MIX_BROADCAST_DRIVER === 'pusher' && process.env.MIX_PUSHER_APP_KEY !== '') {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: process.env.MIX_PUSHER_APP_KEY,
            cluster: process.env.MIX_PUSHER_APP_CLUSTER,
            forceTLS: true,
            encryption: true
        });
    } else if (process.env.MIX_BROADCAST_DRIVER === 'soketi' && process.env.MIX_SOKETI_APP_KEY !== '') {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: process.env.MIX_SOKETI_APP_KEY,
            wsHost: process.env.MIX_SOKETI_HOST,
            wsPort: process.env.MIX_SOKETI_PORT,
            wssPort: process.env.MIX_SOKETI_PORT,
            forceTLS: false,
            encrypted: true,
            disableStats: true,
            enabledTransports: ['ws'],
        }); 
    } else { 
        
    }
}

export { install as default }
