/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./components/**/*.html",
    "./public_html/**/*.html",
  ],
  theme: {
    extend: {
      colors: {
        logoLeft: '#1C99FE',
        logoLeftDark: '#084678',
        logoMiddle: '#7644FF',
        logoRight: '#FD4766',
      }
    },
  },
  plugins: [require("daisyui")],
  daisyui: {
    darkTheme: 'mythemeDark',
    themes: [
      {
        mytheme: {
          "primary": "#2053DB",
          "secondary": "#387EF3",
          "accent": "#37CDBE",
          "neutral": "#3D4451",
          "base-100": "#FFFFFF",
          "info": "#3ABFF8",
          "success": "#36D399",
          "warning": "#FBBD23",
          "error": "#be123c",
        },
        mythemeDark: {
          "primary": "#2563eb",
          "secondary": "#1d4ed8",
          "neutral": "#3D4451",
          "base-100": "#1f2937",
          "info": "#3ABFF8",
          "success": "#36D399",
          "warning": "#FBBD23",
          "error": "#be123c",
        },
      }
    ],
  }
}

