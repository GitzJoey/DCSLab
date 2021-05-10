const state = () => {
  return {
    menu: [
      {
        icon: "HomeIcon",
        pageName: "top-menu-dashboard",
        title: "Dashboard",
        subMenu: [
          {
            icon: "",
            pageName: "top-menu-dashboard-overview-1",
            title: "Overview 1"
          },
          {
            icon: "",
            pageName: "top-menu-dashboard-overview-2",
            title: "Overview 2"
          },
          {
            icon: "",
            pageName: "top-menu-dashboard-overview-3",
            title: "Overview 3"
          }
        ]
      },
      {
        icon: "BoxIcon",
        pageName: "top-menu-menu-layout",
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
        icon: "ActivityIcon",
        pageName: "top-menu-apps",
        title: "Apps",
        subMenu: [
          {
            icon: "UsersIcon",
            pageName: "top-menu-users",
            title: "Users",
            subMenu: [
              {
                icon: "",
                pageName: "top-menu-users-layout-1",
                title: "Layout 1"
              },
              {
                icon: "",
                pageName: "top-menu-users-layout-2",
                title: "Layout 2"
              },
              {
                icon: "",
                pageName: "top-menu-users-layout-3",
                title: "Layout 3"
              }
            ]
          },
          {
            icon: "TrelloIcon",
            pageName: "top-menu-profile",
            title: "Profile",
            subMenu: [
              {
                icon: "",
                pageName: "top-menu-profile-overview-1",
                title: "Overview 1"
              },
              {
                icon: "",
                pageName: "top-menu-profile-overview-2",
                title: "Overview 2"
              },
              {
                icon: "",
                pageName: "top-menu-profile-overview-3",
                title: "Overview 3"
              }
            ]
          },
          {
            icon: "InboxIcon",
            pageName: "top-menu-inbox",
            title: "Inbox"
          },
          {
            icon: "FolderIcon",
            pageName: "top-menu-file-manager",
            title: "File Manager"
          },
          {
            icon: "CreditCardIcon",
            pageName: "top-menu-point-of-sale",
            title: "Point of Sale"
          },
          {
            icon: "MessageSquareIcon",
            pageName: "top-menu-chat",
            title: "Chat"
          },
          {
            icon: "FileTextIcon",
            pageName: "top-menu-post",
            title: "Post"
          },
          {
            icon: "CalendarIcon",
            pageName: "top-menu-calendar",
            title: "Calendar"
          },
          {
            icon: "EditIcon",
            pageName: "top-menu-crud",
            title: "Crud",
            subMenu: [
              {
                icon: "",
                pageName: "top-menu-crud-data-list",
                title: "Data List"
              },
              {
                icon: "",
                pageName: "top-menu-crud-form",
                title: "Form"
              }
            ]
          }
        ]
      },
      {
        icon: "LayoutIcon",
        pageName: "top-menu-layout",
        title: "Pages",
        subMenu: [
          {
            icon: "",
            pageName: "top-menu-wizards",
            title: "Wizards",
            subMenu: [
              {
                icon: "",
                pageName: "top-menu-wizard-layout-1",
                title: "Layout 1"
              },
              {
                icon: "",
                pageName: "top-menu-wizard-layout-2",
                title: "Layout 2"
              },
              {
                icon: "",
                pageName: "top-menu-wizard-layout-3",
                title: "Layout 3"
              }
            ]
          },
          {
            icon: "",
            pageName: "top-menu-blog",
            title: "Blog",
            subMenu: [
              {
                icon: "",
                pageName: "top-menu-blog-layout-1",
                title: "Layout 1"
              },
              {
                icon: "",
                pageName: "top-menu-blog-layout-2",
                title: "Layout 2"
              },
              {
                icon: "",
                pageName: "top-menu-blog-layout-3",
                title: "Layout 3"
              }
            ]
          },
          {
            icon: "",
            pageName: "top-menu-pricing",
            title: "Pricing",
            subMenu: [
              {
                icon: "",
                pageName: "top-menu-pricing-layout-1",
                title: "Layout 1"
              },
              {
                icon: "",
                pageName: "top-menu-pricing-layout-2",
                title: "Layout 2"
              }
            ]
          },
          {
            icon: "",
            pageName: "top-menu-invoice",
            title: "Invoice",
            subMenu: [
              {
                icon: "",
                pageName: "top-menu-invoice-layout-1",
                title: "Layout 1"
              },
              {
                icon: "",
                pageName: "top-menu-invoice-layout-2",
                title: "Layout 2"
              }
            ]
          },
          {
            icon: "",
            pageName: "top-menu-faq",
            title: "FAQ",
            subMenu: [
              {
                icon: "",
                pageName: "top-menu-faq-layout-1",
                title: "Layout 1"
              },
              {
                icon: "",
                pageName: "top-menu-faq-layout-2",
                title: "Layout 2"
              },
              {
                icon: "",
                pageName: "top-menu-faq-layout-3",
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
            pageName: "top-menu-update-profile",
            title: "Update profile"
          },
          {
            icon: "",
            pageName: "top-menu-change-password",
            title: "Change Password"
          }
        ]
      },
      {
        icon: "InboxIcon",
        pageName: "top-menu-components",
        title: "Components",
        subMenu: [
          {
            icon: "",
            pageName: "top-menu-table",
            title: "Table",
            subMenu: [
              {
                icon: "",
                pageName: "top-menu-regular-table",
                title: "Regular Table"
              },
              {
                icon: "",
                pageName: "top-menu-tabulator",
                title: "Tabulator"
              }
            ]
          },
          {
            icon: "",
            pageName: "top-menu-overlay",
            title: "Overlay",
            subMenu: [
              {
                icon: "",
                pageName: "top-menu-modal",
                title: "Modal"
              },
              {
                icon: "",
                pageName: "top-menu-slide-over",
                title: "Slide Over"
              },
              {
                icon: "",
                pageName: "top-menu-notification",
                title: "Notification"
              }
            ]
          },
          {
            icon: "",
            pageName: "top-menu-accordion",
            title: "Accordion"
          },
          {
            icon: "",
            pageName: "top-menu-button",
            title: "Button"
          },
          {
            icon: "",
            pageName: "top-menu-alert",
            title: "Alert"
          },
          {
            icon: "",
            pageName: "top-menu-progress-bar",
            title: "Progress Bar"
          },
          {
            icon: "",
            pageName: "top-menu-tooltip",
            title: "Tooltip"
          },
          {
            icon: "",
            pageName: "top-menu-dropdown",
            title: "Dropdown"
          },
          {
            icon: "",
            pageName: "top-menu-typography",
            title: "Typography"
          },
          {
            icon: "",
            pageName: "top-menu-icon",
            title: "Icon"
          },
          {
            icon: "",
            pageName: "top-menu-loading-icon",
            title: "Loading Icon"
          }
        ]
      },
      {
        icon: "SidebarIcon",
        pageName: "top-menu-forms",
        title: "Forms",
        subMenu: [
          {
            icon: "",
            pageName: "top-menu-regular-form",
            title: "Regular Form"
          },
          {
            icon: "",
            pageName: "top-menu-datepicker",
            title: "Datepicker"
          },
          {
            icon: "",
            pageName: "top-menu-tail-select",
            title: "Tail Select"
          },
          {
            icon: "",
            pageName: "top-menu-file-upload",
            title: "File Upload"
          },
          {
            icon: "",
            pageName: "top-menu-wysiwyg-editor",
            title: "Wysiwyg Editor"
          },
          {
            icon: "",
            pageName: "top-menu-validation",
            title: "Validation"
          }
        ]
      },
      {
        icon: "HardDriveIcon",
        pageName: "top-menu-widgets",
        title: "Widgets",
        subMenu: [
          {
            icon: "",
            pageName: "top-menu-chart",
            title: "Chart"
          },
          {
            icon: "",
            pageName: "top-menu-slider",
            title: "Slider"
          },
          {
            icon: "",
            pageName: "top-menu-image-zoom",
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
