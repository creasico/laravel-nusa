---
layout: home
title: Laravel Nusa
titleTemplate: Indonesian Administrative Region Data for Laravel

hero:
  name: Laravel Nusa
  text: Indonesian Administrative Region Data for Laravel
  tagline: Complete, accurate, and up-to-date Indonesian administrative region data with powerful Laravel integration
  image:
    src: /logo.svg
    alt: Laravel Nusa
  actions:
    - theme: brand
      text: Get Started
      link: /en/guide/getting-started
    - theme: alt
      text: View on GitHub
      link: https://github.com/creasico/laravel-nusa

features:
  - icon: 🗺️
    title: Complete Coverage
    details: All 38 provinces, 514 regencies/cities, 7,285 districts, and 83,762 villages with accurate hierarchical relationships.

  - icon: ⚡
    title: High Performance
    details: Optimized database structure with efficient queries, caching support, and minimal memory footprint.

  - icon: 🔌
    title: Easy Integration
    details: Simple installation, intuitive Eloquent models, and comprehensive API endpoints for seamless integration.

  - icon: 🌐
    title: RESTful API
    details: Complete REST API with pagination, search, filtering, and relationship loading for frontend applications.

  - icon: 📍
    title: Address Management
    details: Built-in address management system with validation, postal code auto-fill, and multiple address support.

  - icon: 🛠️
    title: Highly Customizable
    details: Extend models, add custom relationships, implement traits, and customize API endpoints to fit your needs.
---

<script setup>
import { onMounted } from 'vue'

onMounted(() => {
  // Redirect to English version as default
  if (typeof window !== 'undefined' && window.location.pathname === '/') {
    window.location.replace('/en/')
  }
})
</script>