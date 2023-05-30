import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import path from "path";

export default ({ command, mode }) => {
  process.env = {...process.env, ...loadEnv(mode, process.cwd())};

  return defineConfig({
    build: {
      commonjsOptions: {
        include: ["tailwind.config.js", "node_modules/**"],
      },
    },
    optimizeDeps: {
      include: ["tailwind-config"],
    },
    plugins: [vue()],
    resolve: {
      alias: {
        "tailwind-config": path.resolve(__dirname, "./tailwind.config.js"),
      },
    },
  });
}
