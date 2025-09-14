import 'vuetify/styles'
import { createVuetify } from 'vuetify'


export default createVuetify({
  theme: {
    defaultTheme: 'simulio',
    themes: {
      simulio: {
        dark: false,
        colors: {
          primary: '#2E6BFF',
          'primary-darken-1': '#1F4FD1',
          secondary: '#00B894',
          accent: '#7C4DFF',
          surface: '#FFFFFF',
          background: '#F6F8FB',
          success: '#16A34A',
          info: '#2196F3',
          warning: '#FB8C00',
          error: '#E53935',
        },
        variables: {
          'border-radius': '14px',
        },
      },
    },
  },
  defaults: {
    VTextField: { variant: 'outlined', density: 'comfortable', rounded: 'lg' },
    VSelect:    { variant: 'outlined', density: 'comfortable', rounded: 'lg' },
    VBtn:       { rounded: 'xl', height: 44 },
    VCard:      { rounded: 'xl', elevation: 1 },
    VAlert:     { rounded: 'lg' },
  },
})
