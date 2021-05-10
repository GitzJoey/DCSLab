const state = () => {
  return {
    menu: [
      {
        icon: "HomeIcon",
        pageName: "simple-menu-dashboard",
        title: "Dashboard",
        subMenu: [
          {
            icon: "",
            pageName: "simple-menu-dashboard-overview-1",
            title: "Overview 1"
          },
          {
            icon: "",
            pageName: "simple-menu-dashboard-overview-2",
            title: "Overview 2"
          },
          {
            icon: "",
            pageName: "simple-menu-dashboard-overview-3",
            title: "Overview 3"
          }
        ]
      },
      {
        icon: "BoxIcon",
        pageName: "simple-menu-menu-layout",
        title: "Menu Layout",
        subMenu: [
          {
            icon: "",
            pageName: "side-menu-dashboard-overview-1",
            title: "Side Menu",
            ignore: true
          },
          {
            icon: "",
            pageName: "simple-menu-dashboard-overview-1",
            title: "Simple Menu",
            ignore: true
          },
          {
            icon: "",
            pageName: "top-menu-dashboard-overview-1",
            title: "Top Menu",
            ignore: true
          }
        ]
      },
      {
        icon: "InboxIcon",
        pageName: "simple-menu-inbox",
        title: "Inbox"
      },
      {
        icon: "HardDriveIcon",
        pageName: "simple-menu-file-manager",
        title: "File Manager"
      },
      {
        icon: "CreditCardIcon",
        pageName: "simple-menu-point-of-sale",
        title: "Point of Sale"
      },
      {
        icon: "MessageSquareIcon",
        pageName: "simple-menu-chat",
        title: "Chat"
      },
      {
        icon: "FileTextIcon",
        pageName: "simple-menu-post",
        title: "Post"
      },
      {
        icon: "CalendarIcon",
        pageName: "simple-menu-calendar",
        title: "Calendar"
      },
      "devider",
      {
        icon: "EditIcon",
        pageName: "simple-menu-crud",
        title: "Crud",
        subMenu: [
          {
            icon: "",
            pageName: "simple-menu-crud-data-list",
            title: "Data List"
          },
          {
            icon: "",
            pageName: "simple-menu-crud-form",
            title: "Form"
          }
        ]
      },
      {
        icon: "UsersIcon",
        pageName: "simple-menu-users",
        title: "Users",
        subMenu: [
          {
            icon: "",
            pageName: "simple-menu-users-layout-1",
            title: "Layout 1"
          },
          {
            icon: "",
            pageName: "simple-menu-users-layout-2",
            title: "Layout 2"
          },
          {
            icon: "",
            pageName: "simple-menu-users-layout-3",
            title: "Layout 3"
          }
        ]
      },
      {
        icon: "TrelloIcon",
        pageName: "simple-menu-profile",
        title: "Profile",
        subMenu: [
          {
            icon: "",
            pageName: "simple-menu-profile-overview-1",
            title: "Overview 1"
          },
          {
            icon: "",
            pageName: "simple-menu-profile-overview-2",
            title: "Overview 2"
          },
          {
            icon: "",
            pageName: "simple-menu-profile-overview-3",
            title: "Overview 3"
          }
        ]
      },
      {
        icon: "LayoutIcon",
        pageName: "simple-menu-layout",
        title: "Pages",
        subMenu: [
          {
            icon: "",
            pageName: "simple-menu-wizards",
            title: "Wizards",
            subMenu: [
              {
                icon: "",
                pageName: "simple-menu-wizard-layout-1",
                title: "Layout 1"
              },
              {
                icon: "",
                pageName: "simple-menu-wizard-layout-2",
                title: "Layout 2"
              },
              {
                icon: "",
                pageName: "simple-menu-wizard-layout-3",
                title: "Layout 3"
              }
            ]
          },
          {
            icon: "",
            pageName: "simple-menu-blog",
            title: "Blog",
            subMenu: [
              {
                icon: "",
                pageName: "simple-menu-blog-layout-1",
                title: "Layout 1"
              },
              {
                icon: "",
                pageName: "simple-menu-blog-layout-2",
                title: "Layout 2"
              },
              {
                icon: "",
                pageName: "simple-menu-blog-layout-3",
                title: "Layout 3"
              }
            ]
          },
          {
            icon: "",
            pageName: "simple-menu-pricing",
            title: "Pricing",
            subMenu: [
              {
                icon: "",
                pageName: "simple-menu-pricing-layout-1",
                title: "Layout 1"
              },
              {
                icon: "",
                pageName: "simple-menu-pricing-layout-2",
                title: "Layout 2"
              }
            ]
          },
          {
            icon: "",
            pageName: "simple-menu-invoice",
            title: "Invoice",
            subMenu: [
              {
                icon: "",
                pageName: "simple-menu-invoice-layout-1",
                title: "Layout 1"
              },
              {
                icon: "",
                pageName: "simple-menu-invoice-layout-2",
                title: "Layout 2"
              }
            ]
          },
          {
            icon: "",
            pageName: "simple-menu-faq",
            title: "FAQ",
            subMenu: [
              {
                icon: "",
                pageName: "simple-menu-faq-layout-1",
                title: "Layout 1"
              },
              {
                icon: "",
                pageName: "simple-menu-faq-layout-2",
                title: "Layout 2"
              },
              {
                icon: "",
                pageName: "simple-menu-faq-layout-3",
                title: "Layout 3"
              }
            ]
          },
          {
            icon: "",
            pageName: "login",
            title: "Login"
          },
          {
            icon: "",
            pageName: "register",
            title: "Register"
          },
          {
            icon: "",
            pageName: "error-page",
            title: "Error Page"
          },
          {
            icon: "",
            pageName: "simple-menu-update-profile",
            title: "Update profile"
          },
          {
            icon: "",
            pageName: "simple-menu-change-password",
            title: "Change Password"
          }
        ]
      },
      "devider",
      {
        icon: "InboxIcon",
        pageName: "simple-menu-components",
        title: "Components",
        subMenu: [
          {
            icon: "",
            pageName: "simple-menu-table",
            title: "Table",
            subMenu: [
              {
                icon: "",
                pageName: "simple-menu-regular-table",
                title: "Regular Table"
              },
              {
                icon: "",
                pageName: "simple-menu-tabulator",
                title: "Tabulator"
              }
            ]
          },
          {
            icon: "",
            pageName: "simple-menu-overlay",
            title: "Overlay",
            subMenu: [
              {
                icon: "",
                pageName: "simple-menu-modal",
                title: "Modal"
              },
              {
                icon: "",
                pageName: "simple-menu-slide-over",
                title: "Slide Over"
              },
              {
                icon: "",
                pageName: "simple-menu-notification",
                title: "Notification"
              }
            ]
          },
          {
            icon: "",
            pageName: "simple-menu-accordion",
            title: "Accordion"
          },
          {
            icon: "",
            pageName: "simple-menu-button",
            title: "Button"
          },
          {
            icon: "",
            pageName: "simple-menu-alert",
            title: "Alert"
          },
          {
            icon: "",
            pageName: "simple-menu-progress-bar",
            title: "Progress Bar"
          },
          {
            icon: "",
            pageName: "simple-menu-tooltip",
            title: "Tooltip"
          },
          {
            icon: "",
            pageName: "simple-menu-dropdown",
            title: "Dropdown"
          },
          {
            icon: "",
            pageName: "simple-menu-typography",
            title: "Typography"
          },
          {
            icon: "",
            pageName: "simple-menu-icon",
            title: "Icon"
          },
          {
            icon: "",
            pageName: "simple-menu-loading-icon",
            title: "Loading Icon"
          }
        ]
      },
      {
        icon: "SidebarIcon",
        pageName: "simple-menu-forms",
        title: "Forms",
        subMenu: [
          {
            icon: "",
            pageName: "simple-menu-regular-form",
            title: "Regular Form"
          },
          {
            icon: "",
            pageName: "simple-menu-datepicker",
            title: "Datepicker"
          },
          {
            icon: "",
            pageName: "simple-menu-tail-select",
            title: "Tail Select"
          },
          {
            icon: "",
            pageName: "simple-menu-file-upload",
            title: "File Upload"
          },
          {
            icon: "",
            pageName: "simple-menu-wysiwyg-editor",
            title: "Wysiwyg Editor"
          },
          {
            icon: "",
            pageName: "simple-menu-validation",
            title: "Validation"
          }
        ]
      },
      {
        icon: "HardDriveIcon",
        pageName: "simple-menu-widgets",
        title: "Widgets",
        subMenu: [
          {
            icon: "",
            pageName: "simple-menu-chart",
            title: "Chart"
          },
          {
            icon: "",
            pageName: "simple-menu-slider",
            title: "Slider"
          },
          {
            icon: "",
            pageName: "simple-menu-image-zoom",
            title: "Image Zoom"
          }
        ]
      }
    ]
  };
};

// getters
const getters = {
  menu: state => state.menu
};

// actions
const actions = {};

// mutations
const mutations = {};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
};
