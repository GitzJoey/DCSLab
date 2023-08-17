import Disclosure from "./Disclosure";
import Group from "./Group.vue";
import Button from "./Button.vue";
import Panel from "./Panel.vue";

const DisclosureComponent = Object.assign({}, Disclosure, {
  Group: Group,
  Button: Button,
  Panel: Panel,
});

export default DisclosureComponent;
