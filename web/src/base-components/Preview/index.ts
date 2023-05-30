import Preview from "./Preview.vue";
import Panel from "./Panel.vue";
import Highlight from "./Highlight.vue";

const PreviewComponent = Object.assign({}, Preview, {
  Preview: Preview,
  Panel: Panel,
  Highlight: Highlight,
});

export default PreviewComponent;
