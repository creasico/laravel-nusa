// Sitemap configuration for Laravel Nusa documentation
export default {
  hostname: 'https://nusa.creasi.dev',
  transformItems: (items) => {
    // Add priority and changefreq to different page types
    return items.map((item) => {
      // Homepage gets highest priority
      if (item.url === '/id/' || item.url === '/en/') {
        return {
          ...item,
          priority: 1.0,
          changefreq: 'weekly'
        }
      }

      // Guide pages get high priority
      if (item.url.includes('/guide/')) {
        return {
          ...item,
          priority: 0.9,
          changefreq: 'monthly'
        }
      }

      // API reference pages get medium-high priority
      if (item.url.includes('/api/')) {
        return {
          ...item,
          priority: 0.8,
          changefreq: 'monthly'
        }
      }

      // Example pages get medium priority
      if (item.url.includes('/examples/')) {
        return {
          ...item,
          priority: 0.7,
          changefreq: 'monthly'
        }
      }

      // Default priority for other pages
      return {
        ...item,
        priority: 0.6,
        changefreq: 'monthly'
      }
    })
  }
}
