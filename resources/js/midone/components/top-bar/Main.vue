<template>
    <div class="top-bar -mx-4 px-4 md:mx-0 md:px-0">
        <div class="intro-x dropdown mr-auto sm:mr-6" data-placement="bottom-start">
            <div class="dropdown-toggle notification cursor-pointer" role="button" aria-expanded="false">
                <UmbrellaIcon class="notification__icon dark:text-gray-300" />
            </div>
            <div class="dropdown-menu w-56">
                <div class="dropdown-menu__content box dark:bg-dark-6">
                </div>
            </div>
        </div>

        <div class="-intro-x breadcrumb mr-auto hidden sm:flex">
            <a href="" class="breadcrumb--active"><strong>Company</strong></a>
        </div>

        <div class="intro-x dropdown mr-auto sm:mr-6">
            <div class="dropdown-toggle notification cursor-pointer" role="button" aria-expanded="false">
                <GlobeIcon class="notification__icon dark:text-gray-300" />
            </div>
            <div class="dropdown-menu w-56">
                <div class="dropdown-menu__content box dark:bg-dark-6">
                    <div class="p-2">
                        <a href="" @click.prevent="switchLang('en')" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <img alt="English" :src="assetPath('us.png')" class="w-4 h-4 mr-2" /> <span class="font-medium">English</span>
                        </a>
                    </div>
                    <div class="p-2">
                        <a href="" @click.prevent="switchLang('id')" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <img alt="Bahasa Indonesia" :src="assetPath('id.png')" class="w-4 h-4 mr-2" /> Bahasa Indonesia
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="intro-x dropdown w-8 h-8">
            <div class="dropdown-toggle w-8 h-8 rounded-full overflow-hidden shadow-lg image-fit zoom-in" role="button" aria-expanded="false">
                <img alt="Profile" :src="assetPath('gray200.jpg')" />
            </div>
            <div class="dropdown-menu w-56">
                <div class="dropdown-menu__content box dark:bg-dark-6">
                    <div class="p-4 border-b border-black border-opacity-5 dark:border-dark-3">
                        <div class="font-medium">{{ userContext !== undefined ? userContext.name:'' }}</div>
                        <div class="text-xs text-gray-600 mt-0.5 dark:text-gray-600">{{ userContext.email }}</div>
                    </div>
                    <div class="p-2">
                        <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <UserIcon class="w-4 h-4 mr-2" />
                            {{ t("components.top-bar.profile_ddl.profile") }}
                        </a>
                    </div>
                    <div class="p-2">
                        <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <MailIcon class="w-4 h-4 mr-2" />
                            {{ t("components.top-bar.profile_ddl.inbox") }}
                        </a>
                    </div>
                    <div class="p-2">
                        <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <ActivityIcon class="w-4 h-4 mr-2" />
                            {{ t("components.top-bar.profile_ddl.activity") }}
                        </a>
                    </div>
                    <div class="p-2 border-t border-black border-opacity-5 dark:border-dark-3">
                        <a href="" @click.prevent="logout" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <ToggleRightIcon class="w-4 h-4 mr-2" />
                            {{ t("components.top-bar.profile_ddl.logout") }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {computed, defineComponent, onMounted, ref} from 'vue';
import { useStore } from '../../store';
import mainMixins from '../../mixins/index';

export default defineComponent({
    setup() {
        const store = useStore()
        const userContext = computed(() => store.state.main.userContext )

        const { t, assetPath } = mainMixins();

        const switchLang = (lang) => {
            document.documentElement.lang = lang;
        }

        function logout() {
            axios.post('/logout').then(response => {
                window.location.href = '/';
            }).catch(response, function(e) {
            });
        }

        return {
            t,
            userContext,
            assetPath,
            switchLang,
            logout
        }
    }
});
</script>
