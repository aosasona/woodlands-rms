import preset from "franken-ui/shadcn-ui/preset";
import variables from "franken-ui/shadcn-ui/variables";
import ui from "franken-ui";
import hooks from "franken-ui/shadcn-ui/hooks";

const shadcn = hooks()


/** @type {import('tailwindcss').Config} */
export default {
  content: ["./pages/**/*.{html,php,js}", "./src/**/*.{html,php,js}"],
  theme: {
    extend: {
      container: {
        center: true,
        padding: {
          DEFAULT: "1rem",
          sm: "2rem",
          lg: "4rem",
          xl: "5rem",
          "2xl": "6rem",
        },
      },
      colors: {
        brand: {
          "blue": "var(--color-primary-blue)",
          "purple": "var(--color-primary-purple)",
          "pink": "var(--color-primary-pink)",
          "grey": "var(--color-secondary-grey)",
          "success": "var(--color-success)",
          "notice": "var(--color-notice)",
          "error": "var(--color-error)",
        }
      }
    },
  },
  presets: [preset],
  plugins: [
    variables({ theme: "violet" }),
    ui({
      components: {
        button: {
          hooks: shadcn.button
        },
        'form-range': {
          hooks: {}
        },
        form: {
          hooks: {},
          media: true
        },
        notification: {
          hooks: {},
          media: true
        },
        tooltip: {
          hooks: {}
        },
      }
    })
  ],
}

