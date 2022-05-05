import dom from "@left4code/tw-starter/dist/js/dom";
import TomSelect from "tom-select";
import _, { clone } from "lodash";

const setValue = (el, props) => {
  if (props.modelValue.length) {
    dom(el).val(props.modelValue);
  }
};

const init = (originalEl, clonedEl, props, emit, computedOptions) => {
  // On option add
  if (Array.isArray(props.modelValue)) {
    computedOptions = {
      onOptionAdd: function (value) {
        // Add new option
        const newOption = document.createElement("option");
        newOption.value = value;
        newOption.text = value;
        originalEl.add(newOption);

        // Emit option add
        emit("optionAdd", value);
      },
      ...computedOptions,
    };
  }

  clonedEl.TomSelect = new TomSelect(clonedEl, computedOptions);

  // On change
  clonedEl.TomSelect.on("change", function (selectedItems) {
    emit(
      "update:modelValue",
      Array.isArray(selectedItems) ? [...selectedItems] : selectedItems
    );
  });
};

const getOptions = (options, tempOptions = []) => {
  options.each(function (optionKey, optionEl) {
    if (optionEl instanceof HTMLOptGroupElement) {
      getOptions(dom(optionEl).children(), tempOptions);
    } else {
      tempOptions.push(optionEl);
    }
  });

  return tempOptions;
};

const updateValue = (
  originalEl,
  clonedEl,
  modelValue,
  props,
  emit,
  computedOptions
) => {
  // Remove old options
  for (const [optionKey, option] of Object.entries(
    clonedEl.TomSelect.options
  )) {
    if (
      !getOptions(dom(clonedEl).prev().children()).filter((optionEl) => {
        return optionEl.value === option.value;
      }).length
    ) {
      clonedEl.TomSelect.removeOption(option.value);
    }
  }

  // Add new options
  dom(clonedEl)
    .prev()
    .children()
    .each(function () {
      clonedEl.TomSelect.addOption({
        text: dom(this).text(),
        value: dom(this).attr("value"),
      });
    });

  // Refresh options
  clonedEl.TomSelect.refreshOptions(false);

  // Update value
  if (
    (!Array.isArray(modelValue) &&
      modelValue !== clonedEl.TomSelect.getValue()) ||
    (Array.isArray(modelValue) &&
      !_.isEqual(modelValue, clonedEl.TomSelect.getValue()))
  ) {
    clonedEl.TomSelect.destroy();
    dom(clonedEl).html(dom(clonedEl).prev().html());
    setValue(clonedEl, props);
    init(originalEl, clonedEl, props, emit, computedOptions);
  }
};

export { setValue, init, updateValue };
