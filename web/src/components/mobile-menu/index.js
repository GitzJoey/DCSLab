import { ref } from "vue";
import dom from "@left4code/tw-starter/dist/js/dom";
import { useRouter } from "vue-router";

const activeMobileMenu = ref(false);
const toggleMobileMenu = () => {
  activeMobileMenu.value = !activeMobileMenu.value;
};

const linkTo = (menu, router) => {
  if (menu.subMenu) {
    menu.activeDropdown = !menu.activeDropdown;
  } else {
    activeMobileMenu.value = false;
    const router = useRouter();

    router.push({
      name: menu.pageName,
    });
  }
};

const enter = (el, done) => {
  dom(el).slideDown(300);
};

const leave = (el, done) => {
  dom(el).slideUp(300);
};

export { activeMobileMenu, toggleMobileMenu, linkTo, enter, leave };
