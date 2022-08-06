import Echo from "laravel-echo";
import axios from "@/axios";

const install = app => {
    if (import.meta.env.VITE_BROADCAST_DRIVER === 'pusher' && import.meta.env.VITE_PUSHER_APP_KEY !== '') {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            forceTLS: true,
            encryption: true,
            authorizer: (channel, options) => {
                return {
                    authorize: (socketId, callback) => {
                        axios.post(import.meta.env.VITE_BACKEND_URL + '/broadcasting/auth', {
                            socket_id: socketId,
                            channel_name: channel.name
                        })
                        .then(response => response.json())
                        .then(data => {
                            callback(false, data);
                        }).catch((e) => {
                            callback(true, e);
                        })
                    }
                }
            }
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
            authorizer: (channel, options) => {
                return {
                    authorize: (socketId, callback) => {
                        axios.post(import.meta.env.VITE_BACKEND_URL + '/broadcasting/auth', {
                            socket_id: socketId,
                            channel_name: channel.name
                        })
                        .then(response => response.json())
                        .then(data => {
                            callback(false, data);
                        }).catch((e) => {
                            callback(true, e);
                        })
                    }
                }
            }
        }); 
    } else { 
        
    }
}

export { install as default }
