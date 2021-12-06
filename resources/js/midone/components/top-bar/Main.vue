<template>
    <div class="top-bar -mx-4 px-4 md:mx-0 md:px-0">
        <div id="company-dropdown" class="intro-x dropdown mr-auto sm:mr-6" data-placement="bottom-start">
            <div class="dropdown-toggle notification cursor-pointer" role="button" aria-expanded="false">
                <div class="flex flex-row">
                    <UmbrellaIcon class="notification__icon dark:text-gray-300 mr-2" />
                    <div>{{ selectedCompany }}</div>
                </div>
            </div>
            <div class="dropdown-menu w-56">
                <div class="dropdown-menu__content box dark:bg-dark-6">
                    <div class="p-2" v-for="(c, cIdx) in userCompanyLists">
                        <a href="" @click.prevent="switchCompany(c.hId)" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <span :class="{ 'underline': c.name === selectedCompany, 'font-medium':c.default === 1 }">{{ c.name }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="-intro-x flex items-center text-theme-30 dark:text-white mr-auto hidden sm:flex">
            <a href="" class="text-gray-800 cursor-text dark:text-gray-600"><strong></strong></a>
        </div>

        <div class="mr-auto sm:mr-6 hover:animate-pulse">
            <a id="slide-over" href="javascript:;" data-toggle="modal" data-target="#slide-over-content" class="notification cursor-pointer">
                <ArchiveIcon class="notification__icon dark:text-gray-300" />
            </a>
        </div>

        <div id="language-dropdown" class="intro-x dropdown mr-auto sm:mr-6">
            <div class="dropdown-toggle notification cursor-pointer" role="button" aria-expanded="false">
                <GlobeIcon class="notification__icon dark:text-gray-300" />
            </div>
            <div class="dropdown-menu w-56">
                <div class="dropdown-menu__content box dark:bg-dark-6">
                    <div class="p-2">
                        <a href="" @click.prevent="switchLanguage('en')" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <img alt="English" :src="assetPath('us.png')" class="w-4 h-4 mr-2" /> <span :class="{ 'font-medium': currentLanguage === 'en' }">English</span>
                        </a>
                    </div>
                    <div class="p-2">
                        <a href="" @click.prevent="switchLanguage('id')" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <img alt="Bahasa Indonesia" :src="assetPath('id.png')" class="w-4 h-4 mr-2" /> <span :class="{ 'font-medium': currentLanguage === 'id' }">Bahasa Indonesia</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="main-dropdown" class="intro-x dropdown w-8 h-8">
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
                        <a href="" @click.prevent="goTo('profile')" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <UserIcon class="w-4 h-4 mr-2" />
                            {{ t('components.top-bar.profile_ddl.profile') }}
                        </a>
                    </div>
                    <div class="p-2">
                        <a href="" @click.prevent="goTo('inbox')" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <MailIcon class="w-4 h-4 mr-2" />
                            {{ t('components.top-bar.profile_ddl.inbox') }}
                        </a>
                    </div>
                    <div class="p-2">
                        <a href="" @click.prevent="goTo('activity')" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <ActivityIcon class="w-4 h-4 mr-2" />
                            {{ t('components.top-bar.profile_ddl.activity') }}
                        </a>
                    </div>
                    <div class="p-2 border-t border-black border-opacity-5 dark:border-dark-3">
                        <a href="" @click.prevent="logout" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                            <ToggleRightIcon class="w-4 h-4 mr-2" />
                            {{ t('components.top-bar.profile_ddl.logout') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="slide-over-content" class="modal modal-slide-over" data-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <a data-dismiss="modal" href="javascript:;">
                    <XIcon class="w-8 h-8 text-white"/>
                </a>

                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto"></h2>
                </div>

                <div class="modal-body">
                </div>

                <div class="modal-footer text-right w-full absolute bottom-0">
                    <strong>Copyright &copy; {{ (new Date()).getFullYear() }} <a href="https://www.github.com/GitzJoey">GitzJoey</a>&nbsp;&amp;&nbsp;<a href="#">Contributors</a>.</strong> All rights reserved.<br/> Powered By Coffee &amp; Curiosity.
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, defineComponent, onMounted, ref, watch } from 'vue';
import { switchLang, getLang } from '../../lang/index';
import { useStore } from '../../store';
import mainMixins from '../../mixins/index';
import { useRouter } from "vue-router";

export default defineComponent({
    setup() {
        const store = useStore();
        const router = useRouter();

        const userContext = computed(() => store.state.main.userContext );
        const selectedUserCompanyContext = computed(() => store.state.main.selectedUserCompany );

        const selectedCompany = ref('');

        const userCompanyLists = computed(() => {
            if (userContext.value.companies !== undefined && userContext.value.companies.length > 0) {
                return userContext.value.companies;
            } else {
                return [];
            }
        });

        watch(userContext, () => {
            setSelectedCompany(userContext.value.companies, selectedUserCompanyContext.value);
        });

        watch(selectedUserCompanyContext, () => {
            setSelectedCompany(userContext.value.companies, selectedUserCompanyContext.value);
        });

        const { t, assetPath } = mainMixins();

        const switchLanguage = (lang) => {
            switchLang(lang);
            cash('#language-dropdown').dropdown('hide');
        }

        const currentLanguage = computed(() => {
            return getLang();
        });

        function goTo(page) {
            switch(page) {
                case 'profile':
                    router.push({ name: 'side-menu-dashboard-profile' });
                    break;
                case 'inbox':
                    router.push({ name: 'side-menu-dashboard-inbox' });
                    break;
                case 'activity':
                    router.push({ name: 'side-menu-dashboard-activity' });
                    break;
            }

            cash('#main-dropdown').dropdown('hide');
        }

        function switchCompany(hId) {
            setSelectedCompany(userCompanyLists.value, hId);
            cash('#company-dropdown').dropdown('hide');
        }

        function setSelectedCompany(companyLists, selected) {
            if (companyLists.length === 0) return;

            if (selected === '') {
                let defaultCompany = _.find(companyLists, { default: 1 });
                selectedCompany.value = defaultCompany.name;
                store.dispatch('main/setSelectedCompany', defaultCompany.hId);
            } else {
                _.forEach(companyLists, function(item) {
                    if (selected === item.hId) {
                        selectedCompany.value = item.name;
                        store.dispatch('main/setSelectedCompany', item.hId);
                    }
                });
            }
        }

        function logout() {
            axios.post('/logout').then(response => {
                window.location.href = '/';
            }).catch(e => {

            });
        }

        return {
            t,
            router,
            userContext,
            assetPath,
            switchLanguage,
            currentLanguage,
            userCompanyLists,
            selectedCompany,
            switchCompany,
            logout,
            goTo,
        }
    }
});
</script>
