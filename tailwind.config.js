const primaryColors = require('@left4code/tw-starter/dist/js/colors')

module.exports = {
  mode: "jit",
  purge: [
    './src/**/*.{php,html,js,jsx,ts,tsx,vue}',
    './resources/**/*.{php,html,js,jsx,ts,tsx,vue}',
    './node_modules/@left4code/tw-starter/**/*.js'
  ],
  darkMode: 'class',
  theme: {
    borderColor: (theme) => ({
        ...theme("colors"),
        DEFAULT: primaryColors.gray["300"],
    }),
    colors: {
        ...primaryColors,
        primary: {
            ...primaryColors.primary,
            1: "#2F5AD8",
        },
        white: "white",
        black: "black",
        current: "current",
        transparent: "transparent",
        theme: {
            1: "#162343",
            2: "#94A1B7",
            3: "#263457",
            4: "#DEE7EF",
            5: "#1C3FAA",
            6: "#1B284A",
            7: "#7486A2",
            8: "#1E2C50",
            9: "#172C69",
            10: "#F0F5FF",
            11: "#294CB7",
            12: "#A8B6DB",
            13: "#7E8BB4",
            14: "#BFCBE4",
            15: "#FBC500",
            16: "#E63B1F",
            17: "#B8F1E1",
            18: "#FFE7D9",
            19: "#DBDFF9",
            20: "#13B176",
            21: "#CE3131",
            22: "#203f90",
            23: "#D6E1F5",
            24: "#1C3FAA",
            25: "#2F5AD8",
            26: "#547FFC",
            27: "#16379B",
            28: "#2F50B5",
            29: "#F78B00",
            30: "#15379D",
            31: "#DEE5F5",
            32: "#8DA9BE",
            33: "#607F96",
            34: "#b7c3e6",
            35: "#1b389f",
            36: "#183295",
        },
    },
    extend: {
        fontFamily: {
            roboto: ["Roboto"],
        },
        container: {
            center: true,
        },
        maxWidth: {
            "1/4": "25%",
            "1/2": "50%",
            "3/4": "75%",
        },
        strokeWidth: {
            0.5: 0.5,
            1.5: 1.5,
            2.5: 2.5,
        },
    },
},
variants: {
    extend: {
        boxShadow: ["dark"],
    },
},
};
