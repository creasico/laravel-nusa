import { defineConfig } from 'vitepress'
import { withMermaid } from 'vitepress-plugin-mermaid'

export default withMermaid(defineConfig({
  title: 'Laravel Nusa',
  description: 'Indonesian Administrative Region Data for Laravel',
  base: '/',

  head: [
    ['link', { rel: 'icon', sizes: '32x32', href: '/favicon-32x32.png' }],
    ['link', { rel: 'icon', sizes: 'any', type: 'image/svg+xml', href: '/favicon.svg' }],
    ['link', { rel: 'apple-touch-icon', href: '/apple-touch-icon.png' }],
    ['meta', { name: 'theme-color', content: '#3c82f6' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:locale', content: 'en' }],
    ['meta', { property: 'og:title', content: 'Laravel Nusa | Indonesian Administrative Data' }],
    ['meta', { property: 'og:site_name', content: 'Laravel Nusa' }],
    ['meta', { property: 'og:image', content: '/og-image.png' }],
    ['meta', { property: 'og:url', content: 'https://nusa.creasi.co/' }],
  ],

  themeConfig: {
    logo: '/logo.svg',

    nav: [
      { text: 'Guide', link: '/guide/getting-started' },
      { text: 'API Reference', link: '/api/overview' },
      { text: 'Examples', link: '/examples/basic-usage' },
      {
        text: 'v0.1.14',
        items: [
          { text: 'Changelog', link: 'https://github.com/creasico/laravel-nusa/blob/main/CHANGELOG.md' },
        ]
      }
    ],

    sidebar: {
      '/guide/': [
        {
          text: 'Introduction',
          items: [
            { text: 'What is Laravel Nusa?', link: '/guide/what-is-laravel-nusa' },
            { text: 'Getting Started', link: '/guide/getting-started' },
            { text: 'Installation', link: '/guide/installation' },
            { text: 'Configuration', link: '/guide/configuration' }
          ]
        },
        {
          text: 'Features',
          items: [
            { text: 'Models & Relationships', link: '/guide/models' },
            { text: 'Address Management', link: '/guide/addresses' },
            { text: 'Customization', link: '/guide/customization' },
            { text: 'RESTful API', link: '/guide/api' }
          ]
        },
        {
          text: 'Development',
          items: [
            { text: 'Development Setup', link: '/guide/development' },
            { text: 'Troubleshooting', link: '/guide/troubleshooting' }
          ]
        }
      ],
      '/api/': [
        {
          text: 'API Reference',
          items: [
            { text: 'Overview', link: '/api/overview' },
            { text: 'Provinces', link: '/api/provinces' },
            { text: 'Regencies', link: '/api/regencies' },
            { text: 'Districts', link: '/api/districts' },
            { text: 'Villages', link: '/api/villages' }
          ]
        },
        {
          text: 'Models',
          items: [
            { text: 'Overview', link: '/api/models/overview' },
            { text: 'Province Model', link: '/api/models/province' },
            { text: 'Regency Model', link: '/api/models/regency' },
            { text: 'District Model', link: '/api/models/district' },
            { text: 'Village Model', link: '/api/models/village' },
            { text: 'Address Model', link: '/api/models/address' }
          ]
        },
        {
          text: 'Customization',
          items: [
            { text: 'Model Concerns Overview', link: '/api/concerns/' },
            { text: 'WithProvince', link: '/api/concerns/with-province' },
            { text: 'WithRegency', link: '/api/concerns/with-regency' },
            { text: 'WithDistrict', link: '/api/concerns/with-district' },
            { text: 'WithVillage', link: '/api/concerns/with-village' },
            { text: 'WithDistricts', link: '/api/concerns/with-districts' },
            { text: 'WithVillages', link: '/api/concerns/with-villages' },
            { text: 'WithAddress', link: '/api/concerns/with-address' },
            { text: 'WithAddresses', link: '/api/concerns/with-addresses' },
            { text: 'WithCoordinate', link: '/api/concerns/with-coordinate' }
          ]
        }
      ],
      '/examples/': [
        {
          text: 'Usage Examples',
          items: [
            { text: 'Basic Usage', link: '/examples/basic-usage' },
            { text: 'API Integration', link: '/examples/api-integration' },
            { text: 'Address Forms', link: '/examples/address-forms' },
            { text: 'Geographic Queries', link: '/examples/geographic-queries' },
            { text: 'Custom Models', link: '/examples/custom-models' }
          ]
        }
      ]
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/creasico/laravel-nusa' }
    ],

    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © 2024-present Creasi Developers'
    },

    editLink: {
      pattern: 'https://github.com/creasico/laravel-nusa/edit/main/resources/docs/:path',
      text: 'Edit this page on GitHub'
    },

    search: {
      provider: 'local'
    },

    lastUpdated: {
      text: 'Updated at',
      formatOptions: {
        dateStyle: 'full',
        timeStyle: 'medium'
      }
    }
  },

  markdown: {
    theme: {
      light: 'github-light',
      dark: 'github-dark'
    },
    lineNumbers: false
  }
}))
