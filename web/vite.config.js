import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import path from "path";

export default ({ command, mode }) => {
  process.env = {...process.env, ...loadEnv(mode, process.cwd())};
  
  return defineConfig({
    plugins: [vue()],
    resolve: {
      alias: {
        "@": path.resolve(__dirname, "./src"),
      },
    },
    test: {
      globals: true,
      environment: 'jsdom'
    }
  });
}
