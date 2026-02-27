import FormCheck from "./FormCheck.vue";
import Input from "./Input.vue";
import Label from "./Label.vue";

const FormCheckComponent = Object.assign({}, FormCheck, {
  Input: Input,
  Label: Label,
});

export default FormCheckComponent;
