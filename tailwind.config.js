import preset from "franken-ui/shadcn-ui/preset";
import variables from "franken-ui/shadcn-ui/variables";
import ui from "franken-ui";
import hooks from "franken-ui/shadcn-ui/hooks";

const shadcn = hooks()

/** @type {import('tailwindcss').Config} */
export default {
  content: ["./pages/**/*.{html,php,js}", "./src/**/*.{html,php,js}"],
  theme: {
    extend: {},
  },
  presets: [preset],
  plugins: [variables({ theme: "#81057F" }), ui({
    components: {
      button: {
        hooks: {}
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
      }
    }
  })],
}

