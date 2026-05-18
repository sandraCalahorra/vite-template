const primary = {
  100: '#FEE3C7',
  200: '#FDD3A8',
  300: '#F9B572',
  400: '#F08C30',
  500: '#E47209',
  600: '#D97A23',
  700: '#BA6211',
  800: '#945216',
  900: '#794521',
};

const warning = {
  100: '#FCDFAE',
  200: '#FBD79A',
  300: '#FACF85',
  400: '#F9C671',
  500: '#F8BE5C',
  600: '#F8AB4B',
  700: '#F99939',
  800: '#F98628',
  900: '#F97316',
};

// Add aliases to the primary palette
primary.light = primary[300];
primary['medium-light'] = primary[400];
primary.DEFAULT = primary[500];
primary['medium-dark'] = primary[700];
primary.dark = primary[900];

// Add aliases to the warning palette
warning.light = warning[100];
warning.DEFAULT = warning[500];

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
    theme: {
      extend: {
        boxShadow: {
          'md-primary':'0 4px 6px -1px rgba(0, 151, 169, 0.1), 0 2px 4px -1px rgba(0, 151, 169, 0.06)',
          'lg':'0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.2)',
          'red':'0 7px 10px -3px rgb(143 0 0 / 10%), 0 4px 6px -2px rgb(143 0 0 / 10%)'
        },
        space: {
          '28px': '28px'
        },
        borderWidth: {
            '6': '6px'
        },
        height: {
          '25': '6.25rem',
          '50': '12.5rem',
          'fit': 'fit-content'
        },
        width: {
          '50': '12.5rem',
          '68': '17rem',
          '80': '20rem',
          '109': '27.25rem',
          '160': '40rem',
          '200': '50rem'
        },
        screens: {
          'xs': '375px',
          's-250': '250px',
          's-300': '300px',
          's-420': '420px',
          's-500': '500px',
          's-770': '770px',
          's-900': '900px',
          's-940': '940px',
          's-955': '955px',
          's-1080': '1080px',
          's-1200': '1200px',
          's-1480': '1480px',
          'xxl': '1585px'
        },
        minWidth: {
          '6': '1.5rem',
          '30px': '1.875rem',
          '24': '6rem',
          '32': '8rem',
          '56': '14rem'
        },
        minHeight: {
          '16': '4rem'
        },
        maxWidth: {
          '16': '4rem',
          '40': '10rem',
          '64': '16rem',
        },
        maxHeight: {
          '6': '1.5rem'
        },
        margin: {
            '0.5': '0.125rem',
            '14': '3.5rem',
            '17': '4.25rem',
            '34': '8.5rem'
        },
        padding: {
          '7': '1.75rem'
        },
        inset: {
          '100': '100%'
        },
        gridTemplateColumns: {
          'topbar': 'max-content 1fr',
          'detail': '36rem 1fr',
          'checkbox': "repeat(auto-fill, minmax(7rem, 1fr))"
        },
        colors: {
            primary,
            warning,
            'rojo': "#D7263D",
        },
        divideWidth: {
            '1': '1px',
        },
        zIndex: {
            '5' : '5',
            '60' : '60',
            '70' : '70',
            '80' : '80',
            '90' : '90',
            '100' : '100',
        },
        transitionProperty: {
            'order': 'order',
            'height-width': 'height, width',
        },
        transitionDuration: {
            '700': '700ms',
        },
      }
    },
    variants: {
      textColor: ['responsive', 'group-hover', 'hover', 'focus-within', 'focus']
    },
    plugins: [
    //   require('@tailwindcss/custom-forms'),
      require('@tailwindcss/typography')
    ]
  }
