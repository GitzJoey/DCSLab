import { defineStore } from "pinia";
import { Icon } from "../base-components/Lucide/Lucide.vue";

export interface Menu {
  icon: Icon;
  title: string;
  pageName?: string;
  subMenu?: Menu[];
  ignore?: boolean;
}

export interface TopMenuState {
  menu: Array<Menu>;
}

export const useTopMenuStore = defineStore("topMenu", {
  state: (): TopMenuState => ({
    menu: [
      {
        icon: "Home",
        pageName: "top-menu-dashboard",
        title: "Dashboard",
        subMenu: [
          {
            icon: "Activity",
            pageName: "top-menu-dashboard-overview-1",
            title: "Overview 1",
          },
          {
            icon: "Activity",
            pageName: "top-menu-dashboard-overview-2",
            title: "Overview 2",
          },
          {
            icon: "Activity",
            pageName: "top-menu-dashboard-overview-3",
            title: "Overview 3",
          },
          {
            icon: "Activity",
            pageName: "top-menu-dashboard-overview-4",
            title: "Overview 4",
          },
        ],
      },
      {
        icon: "Box",
        pageName: "top-menu-menu-layout",
        title: "Menu Layout",
        subMenu: [
          {
            icon: "Activity",
            pageName: "side-menu-dashboard-overview-1",
            title: "Side Menu",
            ignore: true,
          },
          {
            icon: "Activity",
            pageName: "simple-menu-dashboard-overview-1",
            title: "Simple Menu",
            ignore: true,
          },
          {
            icon: "Activity",
            pageName: "top-menu-dashboard-overview-1",
            title: "Top Menu",
            ignore: true,
          },
        ],
      },
      {
        icon: "Activity",
        pageName: "top-menu-apps",
        title: "Apps",
        subMenu: [
          {
            icon: "Users",
            pageName: "top-menu-users",
            title: "Users",
            subMenu: [
              {
                icon: "Zap",
                pageName: "top-menu-users-layout-1",
                title: "Layout 1",
              },
              {
                icon: "Zap",
                pageName: "top-menu-users-layout-2",
                title: "Layout 2",
              },
              {
                icon: "Zap",
                pageName: "top-menu-users-layout-3",
                title: "Layout 3",
              },
            ],
          },
          {
            icon: "Trello",
            pageName: "top-menu-profile",
            title: "Profile",
            subMenu: [
              {
                icon: "Zap",
                pageName: "top-menu-profile-overview-1",
                title: "Overview 1",
              },
              {
                icon: "Zap",
                pageName: "top-menu-profile-overview-2",
                title: "Overview 2",
              },
              {
                icon: "Zap",
                pageName: "top-menu-profile-overview-3",
                title: "Overview 3",
              },
            ],
          },
          {
            icon: "ShoppingBag",
            pageName: "top-menu-ecommerce",
            title: "E-Commerce",
            subMenu: [
              {
                icon: "Zap",
                pageName: "top-menu-categories",
                title: "Categories",
              },
              {
                icon: "Zap",
                pageName: "top-menu-add-product",
                title: "Add Product",
              },
              {
                icon: "Zap",
                pageName: "top-menu-product-list",
                title: "Product List",
              },
              {
                icon: "Zap",
                pageName: "top-menu-product-grid",
                title: "Product Grid",
              },
              {
                icon: "Zap",
                pageName: "top-menu-transaction-list",
                title: "Transaction List",
              },
              {
                icon: "Zap",
                pageName: "top-menu-transaction-detail",
                title: "Transaction Detail",
              },
              {
                icon: "Zap",
                pageName: "top-menu-seller-list",
                title: "Seller List",
              },
              {
                icon: "Zap",
                pageName: "top-menu-seller-detail",
                title: "Seller Detail",
              },
              {
                icon: "Zap",
                pageName: "top-menu-reviews",
                title: "Reviews",
              },
            ],
          },
          {
            icon: "Inbox",
            pageName: "top-menu-inbox",
            title: "Inbox",
          },
          {
            icon: "Folder",
            pageName: "top-menu-file-manager",
            title: "File Manager",
          },
          {
            icon: "CreditCard",
            pageName: "top-menu-point-of-sale",
            title: "Point of Sale",
          },
          {
            icon: "MessageSquare",
            pageName: "top-menu-chat",
            title: "Chat",
          },
          {
            icon: "FileText",
            pageName: "top-menu-post",
            title: "Post",
          },
          {
            icon: "Calendar",
            pageName: "top-menu-calendar",
            title: "Calendar",
          },
          {
            icon: "Edit",
            pageName: "top-menu-crud",
            title: "Crud",
            subMenu: [
              {
                icon: "Zap",
                pageName: "top-menu-crud-data-list",
                title: "Data List",
              },
              {
                icon: "Zap",
                pageName: "top-menu-crud-form",
                title: "Form",
              },
            ],
          },
        ],
      },
      {
        icon: "Layout",
        pageName: "top-menu-layout",
        title: "Pages",
        subMenu: [
          {
            icon: "Activity",
            pageName: "top-menu-wizards",
            title: "Wizards",
            subMenu: [
              {
                icon: "Zap",
                pageName: "top-menu-wizard-layout-1",
                title: "Layout 1",
              },
              {
                icon: "Zap",
                pageName: "top-menu-wizard-layout-2",
                title: "Layout 2",
              },
              {
                icon: "Zap",
                pageName: "top-menu-wizard-layout-3",
                title: "Layout 3",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "top-menu-blog",
            title: "Blog",
            subMenu: [
              {
                icon: "Zap",
                pageName: "top-menu-blog-layout-1",
                title: "Layout 1",
              },
              {
                icon: "Zap",
                pageName: "top-menu-blog-layout-2",
                title: "Layout 2",
              },
              {
                icon: "Zap",
                pageName: "top-menu-blog-layout-3",
                title: "Layout 3",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "top-menu-pricing",
            title: "Pricing",
            subMenu: [
              {
                icon: "Zap",
                pageName: "top-menu-pricing-layout-1",
                title: "Layout 1",
              },
              {
                icon: "Zap",
                pageName: "top-menu-pricing-layout-2",
                title: "Layout 2",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "top-menu-invoice",
            title: "Invoice",
            subMenu: [
              {
                icon: "Zap",
                pageName: "top-menu-invoice-layout-1",
                title: "Layout 1",
              },
              {
                icon: "Zap",
                pageName: "top-menu-invoice-layout-2",
                title: "Layout 2",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "top-menu-faq",
            title: "FAQ",
            subMenu: [
              {
                icon: "Zap",
                pageName: "top-menu-faq-layout-1",
                title: "Layout 1",
              },
              {
                icon: "Zap",
                pageName: "top-menu-faq-layout-2",
                title: "Layout 2",
              },
              {
                icon: "Zap",
                pageName: "top-menu-faq-layout-3",
                title: "Layout 3",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "login",
            title: "Login",
          },
          {
            icon: "Activity",
            pageName: "register",
            title: "Register",
          },
          {
            icon: "Activity",
            pageName: "error-page",
            title: "Error Page",
          },
          {
            icon: "Activity",
            pageName: "top-menu-update-profile",
            title: "Update profile",
          },
          {
            icon: "Activity",
            pageName: "top-menu-change-password",
            title: "Change Password",
          },
        ],
      },
      {
        icon: "Inbox",
        pageName: "top-menu-components",
        title: "Components",
        subMenu: [
          {
            icon: "Activity",
            pageName: "top-menu-table",
            title: "Table",
            subMenu: [
              {
                icon: "Zap",
                pageName: "top-menu-regular-table",
                title: "Regular Table",
              },
              {
                icon: "Zap",
                pageName: "top-menu-tabulator",
                title: "Tabulator",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "top-menu-overlay",
            title: "Overlay",
            subMenu: [
              {
                icon: "Zap",
                pageName: "top-menu-modal",
                title: "Modal",
              },
              {
                icon: "Zap",
                pageName: "top-menu-slide-over",
                title: "Slide Over",
              },
              {
                icon: "Zap",
                pageName: "top-menu-notification",
                title: "Notification",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "top-menu-tab",
            title: "Tab",
          },
          {
            icon: "Activity",
            pageName: "top-menu-accordion",
            title: "Accordion",
          },
          {
            icon: "Activity",
            pageName: "top-menu-button",
            title: "Button",
          },
          {
            icon: "Activity",
            pageName: "top-menu-alert",
            title: "Alert",
          },
          {
            icon: "Activity",
            pageName: "top-menu-progress-bar",
            title: "Progress Bar",
          },
          {
            icon: "Activity",
            pageName: "top-menu-tooltip",
            title: "Tooltip",
          },
          {
            icon: "Activity",
            pageName: "top-menu-dropdown",
            title: "Dropdown",
          },
          {
            icon: "Activity",
            pageName: "top-menu-typography",
            title: "Typography",
          },
          {
            icon: "Activity",
            pageName: "top-menu-icon",
            title: "",
          },
          {
            icon: "Activity",
            pageName: "top-menu-loading-icon",
            title: "Loading ",
          },
        ],
      },
      {
        icon: "Sidebar",
        pageName: "top-menu-forms",
        title: "Forms",
        subMenu: [
          {
            icon: "Activity",
            pageName: "top-menu-regular-form",
            title: "Regular Form",
          },
          {
            icon: "Activity",
            pageName: "top-menu-datepicker",
            title: "Datepicker",
          },
          {
            icon: "Activity",
            pageName: "top-menu-tom-select",
            title: "Tom Select",
          },
          {
            icon: "Activity",
            pageName: "top-menu-file-upload",
            title: "File Upload",
          },
          {
            icon: "Activity",
            pageName: "top-menu-wysiwyg-editor",
            title: "Wysiwyg Editor",
          },
          {
            icon: "Activity",
            pageName: "top-menu-validation",
            title: "Validation",
          },
        ],
      },
      {
        icon: "HardDrive",
        pageName: "top-menu-widgets",
        title: "Widgets",
        subMenu: [
          {
            icon: "Activity",
            pageName: "top-menu-chart",
            title: "Chart",
          },
          {
            icon: "Activity",
            pageName: "top-menu-slider",
            title: "Slider",
          },
          {
            icon: "Activity",
            pageName: "top-menu-image-zoom",
            title: "Image Zoom",
          },
        ],
      },
    ],
  }),
});
