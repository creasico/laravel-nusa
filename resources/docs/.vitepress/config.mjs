import { defineConfig } from 'vitepress'
import { withMermaid } from 'vitepress-plugin-mermaid'

export default withMermaid(defineConfig({
  title: 'Laravel Nusa',
  description: 'Indonesian Administrative Region Data for Laravel',
  base: '/laravel-nusa/',

  head: [
    ['link', { rel: 'icon', href: '/laravel-nusa/favicon.ico' }],
    ['meta', { name: 'theme-color', content: '#3c82f6' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:locale', content: 'en' }],
    ['meta', { property: 'og:title', content: 'Laravel Nusa | Indonesian Administrative Data' }],
    ['meta', { property: 'og:site_name', content: 'Laravel Nusa' }],
    ['meta', { property: 'og:image', content: '/laravel-nusa/og-image.png' }],
    ['meta', { property: 'og:url', content: 'https://creasico.github.io/laravel-nusa/' }],
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
          { text: 'Contributing', link: '/contributing' }
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
            { text: 'Installation', link: '/guide/installation' }
          ]
        },
        {
          text: 'Development',
          items: [
            { text: 'Development Setup', link: '/guide/development' },
            { text: 'Troubleshooting', link: '/guide/troubleshooting' }
          ]
        },
        {
          text: 'Core Concepts',
          items: [
            { text: 'Models & Relationships', link: '/guide/models' },
            { text: 'Database Structure', link: '/guide/database' },
            { text: 'Configuration', link: '/guide/configuration' }
          ]
        },
        {
          text: 'Features',
          items: [
            { text: 'RESTful API', link: '/guide/api' },
            { text: 'Address Management', link: '/guide/addresses' }
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
            { text: 'Province Model', link: '/api/models/province' },
            { text: 'Regency Model', link: '/api/models/regency' },
            { text: 'District Model', link: '/api/models/district' },
            { text: 'Village Model', link: '/api/models/village' },
            { text: 'Address Model', link: '/api/models/address' }
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
    lineNumbers: true
  }
}))
