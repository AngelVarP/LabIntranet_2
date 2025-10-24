import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
// tailwind.config.js
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          // azul del logo
          50:"#eef5ff",100:"#d9e9ff",200:"#b6d4ff",300:"#8ebcff",
          400:"#5e9cff",500:"#2f7dff",600:"#1f63db",700:"#1a51b1",
          800:"#1a468f",900:"#1b3b74",
        },
        accent: {
          // naranja del logo
          50:"#fff5ec",100:"#ffe8d1",200:"#ffd0a3",300:"#ffb16d",
          400:"#ff8c33",500:"#ff6d00",600:"#e05e00",700:"#b74b00",
          800:"#8f3b00",900:"#733000",
        },
      },
    },
  },
  plugins: [],
}
