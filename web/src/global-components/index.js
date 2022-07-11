import Litepicker from "./litepicker/Main.vue";
import Tippy from "./tippy/Main.vue";
import TippyContent from "./tippy-content/Main.vue";
import TomSelect from "./tom-select/Main.vue";
import TomSelectAjax from "./tom-select/AjaxMain.vue";
import LoadingIcon from "./loading-icon/Main.vue";
import Notification from "./notification/Main.vue";
import { Modal, ModalHeader, ModalBody, ModalFooter } from "./modal";
import {
  Dropdown,
  DropdownToggle,
  DropdownMenu,
  DropdownContent,
  DropdownItem,
  DropdownHeader,
  DropdownFooter,
  DropdownDivider,
} from "./dropdown";
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from "./tab";
import {
  AccordionGroup,
  AccordionItem,
  Accordion,
  AccordionPanel,
} from "./accordion";
import { Alert } from "./alert";
import LucideIcons from "./lucide";

export default (app) => {
  app.component("Litepicker", Litepicker);
  app.component("Tippy", Tippy);
  app.component("TippyContent", TippyContent);
  app.component("TomSelect", TomSelect);
  app.component("TomSelectAjax", TomSelectAjax);
  app.component("LoadingIcon", LoadingIcon);
  app.component("Notification", Notification);
  app.component("Modal", Modal);
  app.component("ModalHeader", ModalHeader);
  app.component("ModalBody", ModalBody);
  app.component("ModalFooter", ModalFooter);
  app.component("Dropdown", Dropdown);
  app.component("DropdownToggle", DropdownToggle);
  app.component("DropdownMenu", DropdownMenu);
  app.component("DropdownContent", DropdownContent);
  app.component("DropdownItem", DropdownItem);
  app.component("DropdownHeader", DropdownHeader);
  app.component("DropdownFooter", DropdownFooter);
  app.component("DropdownDivider", DropdownDivider);
  app.component("TabGroup", TabGroup);
  app.component("TabList", TabList);
  app.component("Tab", Tab);
  app.component("TabPanels", TabPanels);
  app.component("TabPanel", TabPanel);
  app.component("AccordionGroup", AccordionGroup);
  app.component("AccordionItem", AccordionItem);
  app.component("Accordion", Accordion);
  app.component("AccordionPanel", AccordionPanel);
  app.component("Alert", Alert);

  for (const [key, icon] of Object.entries(LucideIcons)) {
    app.component(key, icon);
  }
};
