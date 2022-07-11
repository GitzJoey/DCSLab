import Echo from 'laravel-echo';

const install = app => {
    if (import.meta.env.VITE_BROADCAST_DRIVER === 'pusher' && import.meta.env.VITE_PUSHER_APP_KEY !== '') {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            forceTLS: true,
            encryption: true
        });
    } else if (import.meta.env.VITE_BROADCAST_DRIVER === 'soketi' && import.meta.env.VITE_SOKETI_APP_KEY !== '') {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_SOKETI_APP_KEY,
            wsHost: import.meta.env.VITE_SOKETI_HOST,
            wsPort: import.meta.env.VITE_SOKETI_PORT,
            wssPort: import.meta.env.VITE_SOKETI_PORT,
            forceTLS: false,
            encrypted: true,
            disableStats: true,
            enabledTransports: ['ws'],
        }); 
    } else { 
        
    }
}

export { install as default }
