import FormSwitch from "./FormSwitch.vue";
import Input from "./Input.vue";
import Label from "./Label.vue";

const FormSwitchComponent = Object.assign({}, FormSwitch, {
  Input: Input,
  Label: Label,
});

export default FormSwitchComponent;
