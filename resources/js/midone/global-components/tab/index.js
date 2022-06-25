import { h, defineComponent, resolveDirective, withDirectives } from "vue";
import dom from "@left4code/tw-starter/dist/js/dom";
import "@left4code/tw-starter/dist/js/tab";

const init = (el, { props, emit }) => {
  const tabPanels = dom(el).find(".tab-content").first();
  const tabPanes = dom(tabPanels).children(".tab-pane");
  const tabList = [];
  const ids = [];

  dom(el)
    .find(".nav")
    .each(function () {
      if (dom(this).closest(".tab-content")[0] !== tabPanels[0]) {
        tabList.push(this);
      }
    });

  tabList.forEach((node) => {
    dom(node)
      .find(".nav-item")
      .each(function (key, el) {
        let id = "_" + Math.random().toString(36).substr(2, 9);
        if (ids[key] !== undefined) {
          id = ids[key];
        } else {
          ids[key] = id;
        }

        dom(this)
          .find(".nav-link")
          .attr({
            "data-tw-target": `#${id}`,
            "aria-controls": id,
            "aria-selected": false,
          });

        if (tabPanes[key] !== undefined) {
          dom(tabPanes[key]).attr({
            id: id,
            "aria-labelledby": `${id}-tab`,
          });
        }

        if (key === props.selectedIndex) {
          const tab = tailwind.Tab.getOrCreateInstance(
            dom(el).find(".nav-link")[0]
          );
          tab.show();
          dom(tabPanes).removeAttr("style");
        }

        const navLink = dom(el).find(".nav-link")[0];
        if (navLink["__initiated"] === undefined) {
          navLink["__initiated"] = true;

          navLink.addEventListener("show.tw.tab", () => {
            emit("change", key);
          });
        }
      });
  });
};

// Tab wrapper
const TabGroup = defineComponent({
  name: "TabGroup",
  props: {
    selectedIndex: {
      type: Number,
      default: 0,
    },
    tag: {
      type: String,
      default: "div",
    },
  },
  directives: {
    tab: {
      mounted(el, { value }) {
        init(el, value);
      },
      updated(el, { value }) {
        init(el, value);
      },
    },
  },
  setup(props, { slots, attrs, emit }) {
    const tabDirective = resolveDirective("tab");
    return () =>
      withDirectives(h(props.tag, slots.default()), [
        [tabDirective, { props, emit }],
      ]);
  },
});

// Tab wrapper
const TabList = defineComponent({
  name: "TabList",
  setup(props, { slots, attrs, emit }) {
    return () =>
      h(
        "ul",
        {
          class: "nav",
          role: "tablist",
        },
        slots.default()
      );
  },
});

const Tab = defineComponent({
  name: "Tab",
  props: {
    fullWidth: {
      type: Boolean,
      default: true,
    },
    tag: {
      type: String,
      default: "a",
    },
    class: {
      type: String,
      default: "",
    },
  },
  setup(props, { slots, attrs, emit }) {
    return () =>
      h(
        "li",
        {
          class: `nav-item ${props.fullWidth ? "flex-1" : ""}`,
          role: "presentation",
        },
        [
          h(
            props.tag,
            {
              class: `nav-link ${props.class}`,
              type: "button",
              role: "tab",
            },
            slots.default()
          ),
        ]
      );
  },
});

const TabPanels = defineComponent({
  name: "TabPanels",
  setup(props, { slots, attrs, emit }) {
    return () =>
      h(
        "div",
        {
          class: "tab-content w-full",
        },
        slots.default()
      );
  },
});

const TabPanel = defineComponent({
  name: "TabPanel",
  setup(props, { slots, attrs, emit }) {
    return () =>
      h(
        "div",
        {
          class: "tab-pane",
          role: "tabpanel",
        },
        slots.default()
      );
  },
});

export { TabGroup, TabList, Tab, TabPanels, TabPanel };
