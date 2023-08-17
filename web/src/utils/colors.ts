import { toRGB } from "./helper";
import tailwindColors from "tailwindcss/colors";
import resolveConfig from "tailwindcss/resolveConfig";
import tailwindConfig from "tailwind-config";
import flatten from "flat";

const twConfig = resolveConfig(tailwindConfig);
const colors = twConfig.theme?.colors;

type DefaultColors = typeof tailwindColors;

/** Extended colors */
interface Colors extends DefaultColors {
  primary: string;
  secondary: string;
  success: string;
  info: string;
  warning: string;
  pending: string;
  danger: string;
  light: string;
  dark: string;
  darkmode: {
    50: string;
    100: string;
    200: string;
    300: string;
    400: string;
    500: string;
    600: string;
    700: string;
    800: string;
    900: string;
  };
}

/** Get a value from Tailwind colors by flatten index, if not available the value will be taken from the CSS variable with (--color-) prefix. */
const getColor = (colorKey: DotNestedKeys<Colors>, opacity: number = 1) => {
  const flattenColors = flatten<
    typeof colors,
    {
      [key: string]: string;
    }
  >(colors);

  if (flattenColors[colorKey].search("var") === -1) {
    return `rgb(${toRGB(flattenColors[colorKey])} / ${opacity})`;
  } else {
    const cssVariableName = `--color-${
      flattenColors[colorKey].split("--color-")[1].split(")")[0]
    }`;
    return `rgb(${getComputedStyle(document.body).getPropertyValue(
      cssVariableName
    )} / ${opacity})`;
  }
};

export { getColor };
