# QR Transfer - Software Design Document

## Table of Contents
1. [Architecture Overview](#1-architecture-overview)
2. [Key Design Principles](#2-key-design-principles)
3. [Page Structure](#3-page-structure)
4. [Development Guidelines](#4-development-guidelines)
5. [Security Considerations](#5-security-considerations)
6. [Translation System](#6-translation-system)

## 1. Architecture Overview

The application follows the MVC (Model-View-Controller) architecture pattern:

```mermaid
graph TD
    U[User] --> C[Controllers]
    C --> M[Models]
    C --> V[Views]
    M --> C
    V --> U
```

### Components
- **Models** (`/models`)
  - Handle data logic and business rules
  - Manage data persistence
  - Implement business logic

- **Views** (`/views`)
  - Present the UI to users
  - Handle layout and styling
  - Implement responsive design

- **Controllers** (`/controllers`)
  - Process user input
  - Coordinate between Models and Views
  - Handle application flow

## 2. Key Design Principles

### 2.1 Routing System

```mermaid
graph LR
    R[Request] --> Router[Router.php]
    Router --> Routes[routes.php]
    Routes --> C[Controller]
    C --> V[View]
```

**Critical Requirements:**
- All routes must be defined in `routes.php`
- Route handling logic must be implemented in `Router.php`
- ⚠️ When adding a new page:
  1. Add route definition to `routes.php`
  2. Update routing logic in `Router.php`

### 2.2 User Interface

#### Desktop Layout
```
+------------------+
|     Header       |
|  Logo     Menu   |
+------------------+
|                  |
|    Main Grid     |
|  +----+ +----+  |
|  |Form| | QR |  |
|  +----+ +----+  |
|                  |
+------------------+
```

#### Mobile Layout
```
+------------------+
| ☰ Logo          |
+------------------+
|                  |
|      Form        |
|                  |
+------------------+
|                  |
|       QR         |
|                  |
+------------------+
```

**Key Features:**
- Header present on all pages (`header.php`)
- Responsive design for:
  - 💻 Desktop: Full layout with right-aligned menu
  - 📱 Mobile (Android/iPhone): Hamburger menu on left
- PicoCSS for consistent styling
- Light background menu

### 2.3 Internationalization

```mermaid
graph TD
    T[Translations] --> EN[English]
```

- Location: `/translations` directory
- Format: PHP array files
- Structure:
  ```php
  return [
      'key' => 'translation',
      // ...
  ];
  ```

**Supported Languages:**
1. en (English)

### 2.4 Form Handling

```mermaid
graph TD
    F[Form] --> V[Validation]
    V --> S[Storage]
    S --> L[Local Storage]
    S --> SS[Session Storage]
```

- Client-side validation: `form-validation.js`
- Data persistence:
  - Favorites: Local Storage
  - Form data: Session Storage

### 2.5 QR Code Features

- Generation: Payment information to QR
- Actions:
  - Download QR code
  - Share QR code
- Favorites system

## 3. Page Structure

```mermaid
graph TD
    H[Home] --> QR[QR Generation]
    H --> F[Form Input]
    G[GDPR] --> P[Privacy Policy]
```

## 4. Development Guidelines

1. **CSS Styling**:
   - All CSS must be placed in `/css/styles.css`
   - Avoid inline styles in HTML/PHP files
   - Use CSS variables for theme colors and spacing
   - Follow BEM (Block-Element-Modifier) naming convention for complex components
   - Always prefer PicoCSS components over custom styles when available

2. **PicoCSS Usage**:
   - Use built-in PicoCSS components (buttons, forms, cards etc.) as the foundation
   - Only create custom styles when no suitable PicoCSS component exists
   - When extending PicoCSS, do so through CSS variables where possible
   - Maintain PicoCSS's design language and spacing system

3. 📱 Responsive Design
   - Test on desktop
   - Test on Android
   - Test on iPhone

4. 🏗️ MVC Pattern
   - Follow separation of concerns
   - Keep controllers thin
   - Use models for business logic

5. 🛣️ Routing
   - Update both routing files
   - Follow existing patterns

6. 🎨 UI Consistency
   - Maintain header across pages
   - Use PicoCSS components
   - Follow responsive patterns

7. 🌐 Internationalization
   - Add translations for all text
   - Test RTL languages
   - Update ALL supported languages when making changes

## 5. Security Considerations

- 🔒 Data Protection
  - No sensitive data in QR codes
  - Essential cookies only
  - GDPR compliance

- 🛡️ Best Practices
  - Input validation
  - XSS prevention
  - CSRF protection

## 6. Translation System

### PHP Translations
1. Managed by LanguageController
2. Stored in `/translations/[lang]/` directories
3. Accessed via `$lang->translate('key')` in PHP

### JavaScript Access
1. **Initialization**:
   - `window.t()` function defined in header.php
   - Preloads common translations (save_favorite, update_favorite)
   ```php
   window.t = function(key) {
       const translations = {
           'save_favorite': '<?= $lang->translate('save_favorite') ?>',
           // ...
       };
       return translations[key] || key;
   };
   ```

2. **Usage**:
   - Directly via `window.t('key')`
   - Through the `translate()` module for better abstraction
   ```javascript
   import { translate } from './modules/translations';
   translate('update_favorite');
   ```

3. **Data Attributes**:
   - Some translations passed via HTML data attributes
   ```php
   <button data-update-text="<?= $lang->translate('update_favorite')">
   ```

### Key Locations
- PHP: `LanguageController.php`, view templates
- JS: `modules/translations.js`, component files

Last updated: 2025-04-08

## Removed Components
- About page (controller, view and route)
- Related menu item

---

*Last updated: 2025-04-07*
