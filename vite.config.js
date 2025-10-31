import tailwindcss from "@tailwindcss/vite";
import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

/* if you're using React */
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        react(),
        symfonyPlugin({ refresh: true }),
        tailwindcss(),
    ],
    build: {
        rollupOptions: {
            input: {
                schedulejs: "./assets/Schedule/index.jsx",
                appjs: "./assets/app.js",
                appcss: "./assets/app.css",
                schedulecss: "./assets/Schedule/style.scss",
            },
        }
    },
    server: {
        cors: true
    }
});
