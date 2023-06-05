import Alert from "./Alert.vue";
import DismissButton from "./DismissButton.vue";

const AlertComponent = Object.assign({}, Alert, {
  DismissButton: DismissButton,
});

export default AlertComponent;
