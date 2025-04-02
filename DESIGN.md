# QR Transfer - Software Design Document

## Table of Contents
1. [Architecture Overview](#1-architecture-overview)
2. [Key Design Principles](#2-key-design-principles)
3. [Page Structure](#3-page-structure)
4. [Development Guidelines](#4-development-guidelines)
5. [Security Considerations](#5-security-considerations)

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
    T --> FR[French]
    T --> NL[Dutch]
    T --> O[Other Languages]
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
    A[About] --> I[App Info]
    G[GDPR] --> P[Privacy Policy]
```

## 4. Development Guidelines

1. 📱 Responsive Design
   - Test on desktop
   - Test on Android
   - Test on iPhone

2. 🏗️ MVC Pattern
   - Follow separation of concerns
   - Keep controllers thin
   - Use models for business logic

3. 🛣️ Routing
   - Update both routing files
   - Follow existing patterns

4. 🎨 UI Consistency
   - Maintain header across pages
   - Use PicoCSS components
   - Follow responsive patterns

5. 🌐 Internationalization
   - Add translations for all text
   - Test RTL languages

## 5. Security Considerations

- 🔒 Data Protection
  - No sensitive data in QR codes
  - Essential cookies only
  - GDPR compliance

- 🛡️ Best Practices
  - Input validation
  - XSS prevention
  - CSRF protection

---

*Last updated: 2025-04-02*
