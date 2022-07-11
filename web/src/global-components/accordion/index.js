import { h, defineComponent, resolveDirective, withDirectives } from "vue";
import dom from "@left4code/tw-starter/dist/js/dom";
import "@left4code/tw-starter/dist/js/accordion";

const init = (el, { props, emit }) => {
  const accordionGroupId = "_" + Math.random().toString(36).substr(2, 9);
  dom(el).attr("id", accordionGroupId);
  dom(el)
    .find(".accordion-item")
    .each(function (key, el) {
      const accordionId = "_" + Math.random().toString(36).substr(2, 9);
      const accordionPanelId = "_" + Math.random().toString(36).substr(2, 9);
      dom(this).find(".accordion-header").attr("id", accordionId);
      dom(this).find(".accordion-button").attr({
        "data-tw-target": accordionPanelId,
        "aria-controls": accordionPanelId,
      });
      dom(this).find(".accordion-collapse").attr({
        id: accordionPanelId,
        "aria-labelledby": accordionId,
        "data-tw-parent": accordionGroupId,
      });

      const accordion = tailwind.Accordion.getOrCreateInstance(
        dom(el).find("[data-tw-toggle='collapse']")[0]
      );

      if (props.selectedIndex === null) {
        accordion.hide();
      } else if (key === props.selectedIndex) {
        accordion.show();
      }

      const accordionButton = dom(el).find(".accordion-header")[0];
      if (accordionButton["__initiated"] === undefined) {
        accordionButton["__initiated"] = true;

        accordionButton.addEventListener("show.tw.accordion", () => {
          emit("change", key);
        });
      }
    });
};

// Accordion wrapper
const AccordionGroup = defineComponent({
  name: "AccordionGroup",
  props: {
    selectedIndex: {
      type: [Number, Object],
      default: 0,
    },
    tag: {
      type: String,
      default: "div",
    },
  },
  directives: {
    accordion: {
      mounted(el, { value }) {
        init(el, value);
      },
      updated(el, { value }) {
        init(el, value);
      },
    },
  },
  setup(props, { slots, attrs, emit }) {
    const accordionDirective = resolveDirective("accordion");
    return () =>
      withDirectives(
        h(
          props.tag,
          {
            class: "accordion",
          },
          slots.default()
        ),
        [[accordionDirective, { props, emit }]]
      );
  },
});

const AccordionItem = defineComponent({
  name: "AccordionItem",
  setup(props, { slots, attrs, emit }) {
    return () =>
      h(
        "div",
        {
          class: "accordion-item",
        },
        slots.default()
      );
  },
});

const Accordion = defineComponent({
  name: "Accordion",
  props: {
    class: {
      type: String,
      default: "",
    },
  },
  setup(props, { slots, attrs, emit }) {
    return () =>
      h(
        "div",
        {
          class: "accordion-header",
        },
        [
          h(
            "button",
            {
              class: `accordion-button collapsed ${props.class}`,
              type: "button",
              "aria-expanded": false,
              "data-tw-toggle": "collapse",
            },
            slots.default()
          ),
        ]
      );
  },
});

const AccordionPanel = defineComponent({
  name: "AccordionPanel",
  props: {
    class: {
      type: String,
      default: "",
    },
  },
  setup(props, { slots, attrs, emit }) {
    return () =>
      h(
        "div",
        {
          class: "accordion-collapse collapse",
        },
        [
          h(
            "div",
            {
              class: `accordion-body ${props.class}`,
            },
            slots.default()
          ),
        ]
      );
  },
});

export { AccordionGroup, AccordionItem, Accordion, AccordionPanel };
