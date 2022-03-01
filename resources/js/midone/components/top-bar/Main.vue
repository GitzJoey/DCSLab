<template>
  <div class="top-bar">
    <nav class="-intro-x mr-auto hidden sm:flex">
        <div id="company-dropdown" class="intro-x dropdown mr-auto sm:mr-6" data-placement="bottom-start">
            <div class="dropdown-toggle notification cursor-pointer" role="button" aria-expanded="false" data-tw-toggle="dropdown">
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
    </nav>

    <div class="mr-auto sm:mr-6 hover:animate-pulse">
      <a id="slide-over" href="javascript:;" data-tw-toggle="modal" data-tw-target="#slide-over-content" class="notification cursor-pointer">
        <ArchiveIcon class="notification__icon dark:text-gray-300" />
      </a>
    </div>

    <div id="language-dropdown" class="intro-x dropdown mr-auto sm:mr-6">
      <div class="dropdown-toggle notification cursor-pointer" role="button" aria-expanded="false" data-tw-toggle="dropdown">
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
      <div class="dropdown-toggle w-8 h-8 rounded-full overflow-hidden shadow-lg image-fit zoom-in" role="button" aria-expanded="false" data-tw-toggle="dropdown">
        <img alt="" :src="assetPath('gray200.jpg')"/>
      </div>
      <div class="dropdown-menu w-56">
        <ul class="dropdown-content bg-primary text-white">
          <li class="p-2">
            <div class="font-medium">{{ userContext !== undefined ? userContext.name:'' }}</div>
            <div class="text-xs text-white/70 mt-0.5 dark:text-slate-500">{{ userContext.email }}</div>
          </li>
          <li><hr class="dropdown-divider border-white/[0.08]" /></li>
          <li>
            <a href="" @click.prevent="goTo('profile')" class="dropdown-item hover:bg-white/5">
              <UserIcon class="w-4 h-4 mr-2" />
              {{ t('components.top-bar.profile_ddl.profile') }}
            </a>
          </li>
          <li>
            <a href="" @click.prevent="goTo('inbox')" class="dropdown-item hover:bg-white/5">
              <MailIcon class="w-4 h-4 mr-2" />
              {{ t('components.top-bar.profile_ddl.inbox') }}
            </a>
          </li>
          <li>
            <a href="" @click.prevent="goTo('activity')" class="dropdown-item hover:bg-white/5">
              <ActivityIcon class="w-4 h-4 mr-2" />
              {{ t('components.top-bar.profile_ddl.activity') }}
            </a>
          </li>
          <li><hr class="dropdown-divider border-white/[0.08]" /></li>
          <li>
            <a href="" @click.prevent="logout" class="dropdown-item hover:bg-white/5">
              <ToggleRightIcon class="w-4 h-4 mr-2" />
              {{ t('components.top-bar.profile_ddl.logout') }}
            </a>
          </li>
        </ul>
      </div>
    </div>

    <div id="slide-over-content" class="modal modal-slide-over" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <a data-tw-dismiss="modal" href="javascript:;">
            <XIcon class="w-8 h-8 text-white" />
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
  </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { switchLang, getLang } from "@/lang";
import { assetPath } from "@/mixins";
import axios from "@/axios";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { useUserContextStore } from "@/stores/user-context";

const { t } = useI18n();
const router = useRouter();

const userContextStore = useUserContextStore();
const userContext = computed(() => userContextStore.userContext);
const selectedUserCompany = computed(() => userContextStore.selectedUserCompany);

const selectedCompany = ref('');

const userCompanyLists = computed(() => {
  if (userContext.value.companies !== undefined && userContext.value.companies.length > 0) {
    return userContext.value.companies;
  } else {
    return [];
  }
});

const switchLanguage = (lang) => {
  switchLang(lang);
}

const currentLanguage = computed(() => {
  return getLang();
});

function switchCompany(hId) {
  setSelectedCompany(userCompanyLists.value, hId);
}

function setSelectedCompany(companyLists, selected) {
  if (companyLists.length === 0) return;

  if (selected === '') {
    let defaultCompany = _.find(companyLists, { default: true });
    selectedCompany.value = defaultCompany.name;
    userContextStore.setSelectedCompany(defaultCompany.hId);
  } else {
    _.forEach(companyLists, function(item) {
      if (selected === item.hId) {
        selectedCompany.value = item.name;
        userContextStore.setSelectedCompany(item.hId);
      }
    });
  }
}

watch(userContext, () => {
  setSelectedCompany(userContext.value.companies, selectedUserCompany.value);
});

watch(selectedUserCompany, () => {
  setSelectedCompany(userContext.value.companies, selectedUserCompany.value);
});
</script>
