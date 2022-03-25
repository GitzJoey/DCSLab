<template>
  <div class="top-bar">
    <nav class="-intro-x mr-auto hidden sm:flex">
        <Dropdown id="company-dropdown" class="intro-x mr-auto sm:mr-6" data-tw-placement="bottom-start">
            <DropdownToggle tag="div" class="notification cursor-pointer" role="button">
                <div class="flex flex-row">
                    <UmbrellaIcon class="notification__icon dark:text-slate-300 mr-2" />
                    <LoadingIcon icon="puff" v-if="selectedCompany === ''"/> <div class="text-gray-700 dark:text-slate-300" v-else><strong>{{ selectedCompany }}</strong></div>
                </div>
            </DropdownToggle>
            <DropdownMenu class="w-56">
                <DropdownContent class="notification-content__box dark:bg-dark-6">
                  <div class="p-2" v-for="(c, cIdx) in userCompanyLists">
                    <a href="" @click.prevent="switchCompany(c.hId)" class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-dark-3 rounded-md">
                      <span :class="{ 'underline': c.name === selectedCompany, 'font-medium': c.default === 1 }">{{ c.name }}</span>
                    </a>
                  </div>
                </DropdownContent>
            </DropdownMenu>            
        </Dropdown>
    </nav>

    <div class="mr-auto sm:mr-6 hover:animate-pulse">
      <a href="" class="notification cursor-pointer" @click.prevent="slideOverShow = true">
        <ArchiveIcon class="notification__icon dark:text-slate-300" />
      </a>
    </div>

    <Dropdown id="language-dropdown" class="intro-x mr-auto sm:mr-6">
      <DropdownToggle tag="div" role="button" class="notification cursor-pointer">
        <GlobeIcon class="notification__icon dark:text-slate-300" />
      </DropdownToggle>
      <DropdownMenu class="w-56">
        <DropdownContent tag="div" class="notification-content__box">
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
        </DropdownContent>
      </DropdownMenu>
    </Dropdown>

    <LoadingIcon icon="puff" v-if="userContext.name === undefined"/>

    <Dropdown id="main-dropdown" class="intro-x w-8 h-8" v-else>
      <DropdownToggle tag="div" role="button" class="dropdown-toggle w-8 h-8 rounded-full overflow-hidden shadow-lg image-fit zoom-in">
        <img alt="" :src="assetPath('profile.png')"/>
      </DropdownToggle>
      <DropdownMenu class="w-56">
        <DropdownContent class="bg-primary text-white">
          <DropdownHeader tag="div" class="!font-normal">
            <div class="font-medium">{{ userContext !== undefined ? userContext.name:'' }}</div>
            <div class="text-xs text-white/70 mt-0.5 dark:text-slate-500">{{ userContext.email }}</div>
          </DropdownHeader>
          <DropdownDivider class="border-white/[0.08]" />
          <DropdownItem @click="goTo('profile')">
            <UserIcon class="w-4 h-4 mr-2" />
            {{ t('components.top-bar.profile_ddl.profile') }}
          </DropdownItem>
          <DropdownItem @click.prevent="goTo('inbox')">
            <MailIcon class="w-4 h-4 mr-2" />
            {{ t('components.top-bar.profile_ddl.inbox') }}
          </DropdownItem>
          <DropdownItem @click.prevent="goTo('activity')">
            <ActivityIcon class="w-4 h-4 mr-2" />
            {{ t('components.top-bar.profile_ddl.activity') }}
          </DropdownItem>
          <DropdownDivider class="border-white/[0.08]" />
          <DropdownItem @click.prevent="logout">
            <ToggleRightIcon class="w-4 h-4 mr-2" />
            {{ t('components.top-bar.profile_ddl.logout') }}
          </DropdownItem>
        </DropdownContent>
      </DropdownMenu>
    </Dropdown>

    <Modal backdrop="static" :slideOver="true" :show="slideOverShow" @hidden="slideOverShow = false">
      <a @click.prevent="slideOverShow = false" class="absolute top-0 left-0 right-auto mt-4 -ml-12" href="">
        <XIcon class="w-8 h-8 text-slate-400" />
      </a>
      <ModalHeader class="p-5">
        <h2 class="font-medium text-base mr-auto"></h2>
      </ModalHeader>
      
      <ModalBody>
        <div></div>  
      </ModalBody>

      <ModalFooter class="w-full absolute bottom-0">
        <strong>Copyright &copy; {{ (new Date()).getFullYear() }} <a href="https://www.github.com/GitzJoey">GitzJoey</a>&nbsp;&amp;&nbsp;<a href="https://github.com/GitzJoey/DCSLab/graphs/contributors">Contributors</a>.</strong> All rights reserved.<br/> Powered By Coffee &amp; Curiosity.
      </ModalFooter>
    </Modal>
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

const slideOverShow = ref(false);

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
  tailwind.Dropdown.getOrCreateInstance(document.querySelector("#language-dropdown")).hide();
}

const currentLanguage = computed(() => {
  return getLang();
});

function switchCompany(hId) {
  setSelectedCompany(userCompanyLists.value, hId);
  tailwind.Dropdown.getOrCreateInstance(document.querySelector("#company-dropdown")).hide();
}

function setSelectedCompany(companyLists, selected) {
  if (companyLists.length === 0) return;

  if (selected === '') {
    let defaultCompany = _.find(companyLists, { default: true });
    selectedCompany.value = defaultCompany.name;
    userContextStore.setSelectedUserCompany(defaultCompany.hId);
  } else {
    _.forEach(companyLists, function(item) {
      if (selected === item.hId) {
        selectedCompany.value = item.name;
        userContextStore.setSelectedUserCompany(item.hId);
      }
    });
  }
}

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
    tailwind.Dropdown.getOrCreateInstance(document.querySelector("#main-dropdown")).hide();
}

function logout() {
  tailwind.Dropdown.getOrCreateInstance(document.querySelector("#main-dropdown")).hide();
  axios.post('/logout').then(response => {
    window.location.href = '/';
  }).catch(e => {

  });
}

watch(userContext, () => {
  setSelectedCompany(userContext.value.companies, selectedUserCompany.value);
});

watch(selectedUserCompany, () => {
  setSelectedCompany(userContext.value.companies, selectedUserCompany.value);
});
</script>
