import Tab from "./Tab";
import Button from "./Button.vue";
import Group from "./Group.vue";
import List from "./List.vue";
import Panels from "./Panels.vue";
import Panel from "./Panel.vue";

const TabComponent = Object.assign({}, Tab, {
  Button: Button,
  Group: Group,
  List: List,
  Panels: Panels,
  Panel: Panel,
});

export default TabComponent;
