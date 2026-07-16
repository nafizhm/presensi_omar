/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        ink: "#0E2A2E",
        "ink-soft": "#4A5D5A",
        paper: "#F3F6F4",
        "paper-alt": "#E7ECE9",
        teal: {
          DEFAULT: "#0D9488",
          dark: "#0B7A70",
          tint: "#E3F3F1",
        },
        amber: {
          DEFAULT: "#F59E0B",
          tint: "#FDF1DC",
        },
        coral: {
          DEFAULT: "#E4572E",
          tint: "#FBE7E0",
        },
        line: "#DDE4E1",
      },
      fontFamily: {
        display: ["'Space Grotesk'", "sans-serif"],
        body: ["'Inter'", "sans-serif"],
        mono: ["'IBM Plex Mono'", "monospace"],
      },
    },
  },
  plugins: [],
};
