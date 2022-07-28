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
    server: {
      port: 3000
    },
    test: {
      globals: true,
      environment: 'jsdom',
      include: ['./tests/**/*.test.js']
    }
  });
}
