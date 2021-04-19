import { createApp } from 'vue';
import { createI18n }from 'vue-i18n';
import Role from './components/Role';

let lang = document.documentElement.lang;

const messages = {
    en: {
        table: {
            title: 'Role Lists',
            cols: {
                name: 'Name',
                display_name: 'Display Name',
                description: 'Description',
            }
        },
        buttons: {
            submit: 'Submit',
            reset: 'Reset',
            back: 'Back',
        },
        actions: {
            create: 'Create Role',
            edit: 'Edit Role',
            show: 'Show Role',
            delete: 'Delete Role',
        },
        fields: {
            name: 'Name',
            display_name: 'Display Name',
            description: 'Description',
            permissions: 'Permissions',
        },
        errors: {
            warning: 'Warning',
        }
    },
    id: {
        table: {
            title: 'Daftar Peran',
            cols: {
                name: 'Nama',
                display_name: 'Tampilan',
                description: 'Deskripsi',
            }
        },
        buttons: {
            submit: 'Kirim',
            reset: 'Reset',
            back: 'Kembali',
        },
        actions: {
            create: 'Tambah Peran',
            edit: 'Ubah Peran',
            show: 'Tampilkan Peran',
            delete: 'Hapus Peran',
        },
        fields: {
            name: 'Nama',
            display_name: 'Nama Tampilan',
            description: 'Deskripsi',
            permissions: 'Izin',
        },
        errors: {
            warning: 'Peringatan',
        }
    }
};

const i18n = createI18n({
    locale: lang,
    fallbackLocale: 'en',
    messages,
});

createApp(Role)
    .use(i18n)
    .mount('#roleVue')
