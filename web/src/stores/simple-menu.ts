import { defineStore } from "pinia";
import { Icon } from "../base-components/Lucide/Lucide.vue";

export interface Menu {
  icon: Icon;
  title: string;
  pageName?: string;
  subMenu?: Menu[];
  ignore?: boolean;
}

export interface SimpleMenuState {
  menu: Array<Menu | "divider">;
}

export const useSimpleMenuStore = defineStore("simpleMenu", {
  state: (): SimpleMenuState => ({
    menu: [
      {
        icon: "Home",
        pageName: "simple-menu-dashboard",
        title: "Dashboard",
        subMenu: [
          {
            icon: "Activity",
            pageName: "simple-menu-dashboard-overview-1",
            title: "Overview 1",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-dashboard-overview-2",
            title: "Overview 2",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-dashboard-overview-3",
            title: "Overview 3",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-dashboard-overview-4",
            title: "Overview 4",
          },
        ],
      },
      {
        icon: "Box",
        pageName: "simple-menu-menu-layout",
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
        icon: "ShoppingBag",
        pageName: "simple-menu-ecommerce",
        title: "E-Commerce",
        subMenu: [
          {
            icon: "Activity",
            pageName: "simple-menu-categories",
            title: "Categories",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-add-product",
            title: "Add Product",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-products",
            title: "Products",
            subMenu: [
              {
                icon: "Zap",
                pageName: "simple-menu-product-list",
                title: "Product List",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-product-grid",
                title: "Product Grid",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "simple-menu-transactions",
            title: "Transactions",
            subMenu: [
              {
                icon: "Zap",
                pageName: "simple-menu-transaction-list",
                title: "Transaction List",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-transaction-detail",
                title: "Transaction Detail",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "simple-menu-sellers",
            title: "Sellers",
            subMenu: [
              {
                icon: "Zap",
                pageName: "simple-menu-seller-list",
                title: "Seller List",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-seller-detail",
                title: "Seller Detail",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "simple-menu-reviews",
            title: "Reviews",
          },
        ],
      },
      {
        icon: "Inbox",
        pageName: "simple-menu-inbox",
        title: "Inbox",
      },
      {
        icon: "HardDrive",
        pageName: "simple-menu-file-manager",
        title: "File Manager",
      },
      {
        icon: "CreditCard",
        pageName: "simple-menu-point-of-sale",
        title: "Point of Sale",
      },
      {
        icon: "MessageSquare",
        pageName: "simple-menu-chat",
        title: "Chat",
      },
      {
        icon: "FileText",
        pageName: "simple-menu-post",
        title: "Post",
      },
      {
        icon: "Calendar",
        pageName: "simple-menu-calendar",
        title: "Calendar",
      },
      "divider",
      {
        icon: "Edit",
        pageName: "simple-menu-crud",
        title: "Crud",
        subMenu: [
          {
            icon: "Activity",
            pageName: "simple-menu-crud-data-list",
            title: "Data List",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-crud-form",
            title: "Form",
          },
        ],
      },
      {
        icon: "Users",
        pageName: "simple-menu-users",
        title: "Users",
        subMenu: [
          {
            icon: "Activity",
            pageName: "simple-menu-users-layout-1",
            title: "Layout 1",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-users-layout-2",
            title: "Layout 2",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-users-layout-3",
            title: "Layout 3",
          },
        ],
      },
      {
        icon: "Trello",
        pageName: "simple-menu-profile",
        title: "Profile",
        subMenu: [
          {
            icon: "Activity",
            pageName: "simple-menu-profile-overview-1",
            title: "Overview 1",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-profile-overview-2",
            title: "Overview 2",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-profile-overview-3",
            title: "Overview 3",
          },
        ],
      },
      {
        icon: "Layout",
        pageName: "simple-menu-layout",
        title: "Pages",
        subMenu: [
          {
            icon: "Activity",
            pageName: "simple-menu-wizards",
            title: "Wizards",
            subMenu: [
              {
                icon: "Zap",
                pageName: "simple-menu-wizard-layout-1",
                title: "Layout 1",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-wizard-layout-2",
                title: "Layout 2",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-wizard-layout-3",
                title: "Layout 3",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "simple-menu-blog",
            title: "Blog",
            subMenu: [
              {
                icon: "Zap",
                pageName: "simple-menu-blog-layout-1",
                title: "Layout 1",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-blog-layout-2",
                title: "Layout 2",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-blog-layout-3",
                title: "Layout 3",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "simple-menu-pricing",
            title: "Pricing",
            subMenu: [
              {
                icon: "Zap",
                pageName: "simple-menu-pricing-layout-1",
                title: "Layout 1",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-pricing-layout-2",
                title: "Layout 2",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "simple-menu-invoice",
            title: "Invoice",
            subMenu: [
              {
                icon: "Zap",
                pageName: "simple-menu-invoice-layout-1",
                title: "Layout 1",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-invoice-layout-2",
                title: "Layout 2",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "simple-menu-faq",
            title: "FAQ",
            subMenu: [
              {
                icon: "Zap",
                pageName: "simple-menu-faq-layout-1",
                title: "Layout 1",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-faq-layout-2",
                title: "Layout 2",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-faq-layout-3",
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
            pageName: "simple-menu-update-profile",
            title: "Update profile",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-change-password",
            title: "Change Password",
          },
        ],
      },
      "divider",
      {
        icon: "Inbox",
        pageName: "simple-menu-components",
        title: "Components",
        subMenu: [
          {
            icon: "Activity",
            pageName: "simple-menu-table",
            title: "Table",
            subMenu: [
              {
                icon: "Zap",
                pageName: "simple-menu-regular-table",
                title: "Regular Table",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-tabulator",
                title: "Tabulator",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "simple-menu-overlay",
            title: "Overlay",
            subMenu: [
              {
                icon: "Zap",
                pageName: "simple-menu-modal",
                title: "Modal",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-slide-over",
                title: "Slide Over",
              },
              {
                icon: "Zap",
                pageName: "simple-menu-notification",
                title: "Notification",
              },
            ],
          },
          {
            icon: "Activity",
            pageName: "simple-menu-tab",
            title: "Tab",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-accordion",
            title: "Accordion",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-button",
            title: "Button",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-alert",
            title: "Alert",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-progress-bar",
            title: "Progress Bar",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-tooltip",
            title: "Tooltip",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-dropdown",
            title: "Dropdown",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-typography",
            title: "Typography",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-icon",
            title: "",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-loading-icon",
            title: "Loading ",
          },
        ],
      },
      {
        icon: "Sidebar",
        pageName: "simple-menu-forms",
        title: "Forms",
        subMenu: [
          {
            icon: "Activity",
            pageName: "simple-menu-regular-form",
            title: "Regular Form",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-datepicker",
            title: "Datepicker",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-tom-select",
            title: "Tom Select",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-file-upload",
            title: "File Upload",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-wysiwyg-editor",
            title: "Wysiwyg Editor",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-validation",
            title: "Validation",
          },
        ],
      },
      {
        icon: "HardDrive",
        pageName: "simple-menu-widgets",
        title: "Widgets",
        subMenu: [
          {
            icon: "Activity",
            pageName: "simple-menu-chart",
            title: "Chart",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-slider",
            title: "Slider",
          },
          {
            icon: "Activity",
            pageName: "simple-menu-image-zoom",
            title: "Image Zoom",
          },
        ],
      },
    ],
  }),
});
