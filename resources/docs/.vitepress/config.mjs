import { defineConfig } from 'vitepress'
import { withMermaid } from 'vitepress-plugin-mermaid'

export default withMermaid(defineConfig({
  title: 'Laravel Nusa',
  description: 'Indonesian Administrative Region Data for Laravel',
  base: '/',

  // Default language (English)
  lang: 'en',

  // Rewrites for redirecting old paths to new language-specific paths
  rewrites: {
    'guide/:page': 'en/guide/:page',
    'api/:page*': 'en/api/:page*',
    'examples/:page': 'en/examples/:page'
  },

  // Internationalization
  locales: {
    root: {
      label: 'English',
      lang: 'en',
      title: 'Laravel Nusa',
      description: 'Indonesian Administrative Region Data for Laravel',
      link: '/en/',
      themeConfig: {
        nav: [
          { text: 'Guide', link: '/en/guide/getting-started' },
          { text: 'API Reference', link: '/en/api/overview' },
          { text: 'Examples', link: '/en/examples/basic-usage' },
          {
            text: 'v0.1.14',
            items: [
              { text: 'Changelog', link: 'https://github.com/creasico/laravel-nusa/blob/main/CHANGELOG.md' },
              { text: 'Contributing', link: 'https://github.com/creasico/laravel-nusa/blob/main/CONTRIBUTING.md' }
            ]
          }
        ],
        sidebar: {
          '/en/guide/': [
            {
              text: 'Getting Started',
              items: [
                { text: 'What is Laravel Nusa?', link: '/en/guide/what-is-laravel-nusa' },
                { text: 'Getting Started', link: '/en/guide/getting-started' },
                { text: 'Installation', link: '/en/guide/installation' },
                { text: 'Configuration', link: '/en/guide/configuration' }
              ]
            },
            {
              text: 'Features',
              items: [
                { text: 'Models & Relationships', link: '/en/guide/models' },
                { text: 'Address Management', link: '/en/guide/addresses' },
                { text: 'Customization', link: '/en/guide/customization' },
                { text: 'RESTful API', link: '/en/guide/api' }
              ]
            },
            {
              text: 'Development',
              items: [
                { text: 'Development Setup', link: '/en/guide/development' },
                { text: 'Troubleshooting', link: '/en/guide/troubleshooting' }
              ]
            }
          ],
          '/en/api/': [
            {
              text: 'API Reference',
              items: [
                { text: 'Overview', link: '/en/api/overview' },
                { text: 'Provinces', link: '/en/api/provinces' },
                { text: 'Regencies', link: '/en/api/regencies' },
                { text: 'Districts', link: '/en/api/districts' },
                { text: 'Villages', link: '/en/api/villages' }
              ]
            },
            {
              text: 'Models',
              items: [
                { text: 'Overview', link: '/en/api/models/overview' },
                { text: 'Province Model', link: '/en/api/models/province' },
                { text: 'Regency Model', link: '/en/api/models/regency' },
                { text: 'District Model', link: '/en/api/models/district' },
                { text: 'Village Model', link: '/en/api/models/village' },
                { text: 'Address Model', link: '/en/api/models/address' }
              ]
            },
            {
              text: 'Customization',
              items: [
                { text: 'Model Concerns Overview', link: '/en/api/concerns/' },
                { text: 'WithProvince', link: '/en/api/concerns/with-province' },
                { text: 'WithRegency', link: '/en/api/concerns/with-regency' },
                { text: 'WithDistrict', link: '/en/api/concerns/with-district' },
                { text: 'WithVillage', link: '/en/api/concerns/with-village' },
                { text: 'WithDistricts', link: '/en/api/concerns/with-districts' },
                { text: 'WithVillages', link: '/en/api/concerns/with-villages' },
                { text: 'WithAddress', link: '/en/api/concerns/with-address' },
                { text: 'WithAddresses', link: '/en/api/concerns/with-addresses' },
                { text: 'WithCoordinate', link: '/en/api/concerns/with-coordinate' }
              ]
            }
          ],
          '/en/examples/': [
            {
              text: 'Usage Examples',
              items: [
                { text: 'Basic Usage', link: '/en/examples/basic-usage' },
                { text: 'API Integration', link: '/en/examples/api-integration' },
                { text: 'Address Forms', link: '/en/examples/address-forms' },
                { text: 'Geographic Queries', link: '/en/examples/geographic-queries' },
                { text: 'Custom Models', link: '/en/examples/custom-models' }
              ]
            }
          ]
        }
      }
    },
    id: {
      label: 'Bahasa Indonesia',
      lang: 'id',
      title: 'Laravel Nusa',
      description: 'Data Wilayah Administratif Indonesia untuk Laravel',
      themeConfig: {
        nav: [
          { text: 'Panduan', link: '/id/guide/getting-started' },
          { text: 'Referensi API', link: '/id/api/overview' },
          { text: 'Contoh', link: '/id/examples/basic-usage' },
          {
            text: 'v0.1.14',
            items: [
              { text: 'Changelog', link: 'https://github.com/creasico/laravel-nusa/blob/main/CHANGELOG.md' },
              { text: 'Contributing', link: 'https://github.com/creasico/laravel-nusa/blob/main/CONTRIBUTING.md' }
            ]
          }
        ],
        sidebar: {
          '/id/guide/': [
            {
              text: 'Memulai',
              items: [
                { text: 'Apa itu Laravel Nusa?', link: '/id/guide/what-is-laravel-nusa' },
                { text: 'Memulai', link: '/id/guide/getting-started' },
                { text: 'Instalasi', link: '/id/guide/installation' },
                { text: 'Konfigurasi', link: '/id/guide/configuration' }
              ]
            },
            {
              text: 'Fitur',
              items: [
                { text: 'Model & Relasi', link: '/id/guide/models' },
                { text: 'Manajemen Alamat', link: '/id/guide/addresses' },
                { text: 'Kustomisasi', link: '/id/guide/customization' },
                { text: 'RESTful API', link: '/id/guide/api' }
              ]
            },
            {
              text: 'Pengembangan',
              items: [
                { text: 'Setup Pengembangan', link: '/id/guide/development' },
                { text: 'Troubleshooting', link: '/id/guide/troubleshooting' }
              ]
            }
          ],
          '/id/api/': [
            {
              text: 'Referensi API',
              items: [
                { text: 'Ikhtisar', link: '/id/api/overview' },
                { text: 'Provinsi', link: '/id/api/provinces' },
                { text: 'Kabupaten/Kota', link: '/id/api/regencies' },
                { text: 'Kecamatan', link: '/id/api/districts' },
                { text: 'Kelurahan/Desa', link: '/id/api/villages' }
              ]
            },
            {
              text: 'Model',
              items: [
                { text: 'Ikhtisar', link: '/id/api/models/overview' },
                { text: 'Model Provinsi', link: '/id/api/models/province' },
                { text: 'Model Kabupaten/Kota', link: '/id/api/models/regency' },
                { text: 'Model Kecamatan', link: '/id/api/models/district' },
                { text: 'Model Kelurahan/Desa', link: '/id/api/models/village' },
                { text: 'Model Alamat', link: '/id/api/models/address' }
              ]
            },
            {
              text: 'Kustomisasi',
              items: [
                { text: 'Ikhtisar Model Concerns', link: '/id/api/concerns/' },
                { text: 'WithProvince', link: '/id/api/concerns/with-province' },
                { text: 'WithRegency', link: '/id/api/concerns/with-regency' },
                { text: 'WithDistrict', link: '/id/api/concerns/with-district' },
                { text: 'WithVillage', link: '/id/api/concerns/with-village' },
                { text: 'WithDistricts', link: '/id/api/concerns/with-districts' },
                { text: 'WithVillages', link: '/id/api/concerns/with-villages' },
                { text: 'WithAddress', link: '/id/api/concerns/with-address' },
                { text: 'WithAddresses', link: '/id/api/concerns/with-addresses' },
                { text: 'WithCoordinate', link: '/id/api/concerns/with-coordinate' }
              ]
            }
          ],
          '/id/examples/': [
            {
              text: 'Contoh Penggunaan',
              items: [
                { text: 'Penggunaan Dasar', link: '/id/examples/basic-usage' },
                { text: 'Integrasi API', link: '/id/examples/api-integration' },
                { text: 'Form Alamat', link: '/id/examples/address-forms' },
                { text: 'Query Geografis', link: '/id/examples/geographic-queries' },
                { text: 'Model Kustom', link: '/id/examples/custom-models' }
              ]
            }
          ]
        }
      }
    }
  },

  head: [
    ['link', { rel: 'icon', sizes: '32x32', href: '/favicon-32x32.png' }],
    ['link', { rel: 'icon', sizes: 'any', type: 'image/svg+xml', href: '/favicon.svg' }],
    ['link', { rel: 'apple-touch-icon', href: '/apple-touch-icon.png' }],
    ['meta', { name: 'theme-color', content: '#3c82f6' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:locale', content: 'en_US' }],
    ['meta', { property: 'og:locale:alternate', content: 'id_ID' }],
    ['meta', { property: 'og:title', content: 'Laravel Nusa | Indonesian Administrative Data' }],
    ['meta', { property: 'og:site_name', content: 'Laravel Nusa' }],
    ['meta', { property: 'og:image', content: '/og-image.png' }],
    ['meta', { property: 'og:url', content: 'https://nusa.creasi.co/' }],
    ['meta', { name: 'twitter:card', content: 'summary_large_image' }],
    ['meta', { name: 'twitter:site', content: '@laravelnusa' }],
  ],

  // Sitemap generation for SEO
  sitemap: {
    hostname: 'https://nusa.creasi.co',
    transformItems: (items) => {
      return items.map((item) => {
        // Homepage gets highest priority
        if (item.url === '/' || item.url === '/id/') {
          return { ...item, priority: 1.0, changefreq: 'weekly' }
        }
        // Guide pages get high priority
        if (item.url.includes('/guide/') || item.url.includes('/id/guide/')) {
          return { ...item, priority: 0.9, changefreq: 'monthly' }
        }
        // API reference pages get medium-high priority
        if (item.url.includes('/api/')) {
          return { ...item, priority: 0.8, changefreq: 'monthly' }
        }
        // Example pages get medium priority
        if (item.url.includes('/examples/') || item.url.includes('/id/examples/')) {
          return { ...item, priority: 0.7, changefreq: 'monthly' }
        }
        // Default priority
        return { ...item, priority: 0.6, changefreq: 'monthly' }
      })
    }
  },

  themeConfig: {
    logo: '/logo.svg',

    nav: [
      { text: 'Guide', link: '/en/guide/getting-started' },
      { text: 'API Reference', link: '/en/api/overview' },
      { text: 'Examples', link: '/en/examples/basic-usage' },
      {
        text: 'v0.1.14',
        items: [
          { text: 'Changelog', link: 'https://github.com/creasico/laravel-nusa/blob/main/CHANGELOG.md' },
        ]
      }
    ],



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
      provider: 'local',
      options: {
        locales: {
          root: {
            translations: {
              button: {
                buttonText: 'Search',
                buttonAriaLabel: 'Search docs'
              },
              modal: {
                noResultsText: 'No results for',
                resetButtonTitle: 'Clear search',
                footer: {
                  selectText: 'select',
                  navigateText: 'navigate'
                }
              }
            }
          },
          id: {
            translations: {
              button: {
                buttonText: 'Cari',
                buttonAriaLabel: 'Cari dokumentasi'
              },
              modal: {
                noResultsText: 'Tidak ada hasil untuk',
                resetButtonTitle: 'Reset pencarian',
                footer: {
                  selectText: 'pilih',
                  navigateText: 'navigasi'
                }
              }
            }
          }
        }
      }
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
