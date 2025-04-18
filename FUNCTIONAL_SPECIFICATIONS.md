# **Paid!** - Functional Specifications

## Table of Contents
1. [Form Behavior](#1-form-behavior)
2. [Button Layout](#2-button-layout)
3. [QR Code Generation](#3-qr-code-generation)
4. [Favorites System](#4-favorites-system)
5. [Translation System](#5-translation-system)
6. [Security Requirements](#6-security-requirements)
7. [Testing Requirements](#7-testing-requirements)

## 1. Form Behavior

### 1.1 Input Fields
- Name field: Optional
- IBAN field: Required, validated against IBAN format
- Amount field: Required, numeric validation
- Communication field: Optional

### 1.2 Validation
- Client-side validation using form-validation.js
- Real-time validation feedback
- Error messages displayed in user's language

## 2. Button Layout

### 2.1 Primary Button
- Generate QR button is always visible
- Takes full width on mobile
- Maximum width of 600px on desktop

### 2.2 Secondary Buttons
- Clear form, Save favorite, Delete favorite buttons
- Always displayed in a single horizontal row
- Equal width distribution
- Consistent spacing using PicoCSS gap utility
- Maintains horizontal layout on all screen sizes
- Disabled state for delete button when no favorite is selected

## 3. QR Code Generation

### 3.1 Generation Process
- Triggered by primary button click
- Validates form data before generation
- Shows loading state during generation
- Displays generated QR code in right column
- Re-enables generate button after successful generation
- Resets button text to original state

### 3.2 QR Code Display
- Responsive sizing
- Maximum width of 400px
- Centered alignment
- Download and share capabilities

## 4. Favorites System

### 4.1 Storage
- Local storage for favorites
- JSON format storage
- Maximum of 10 favorites

### 4.2 Button Behavior
- Save button changes to Update when editing a favorite
- Delete button only enabled when a favorite is selected
- Automatic QR generation when selecting a favorite

## 5. Translation System

- The application supports the following languages for all user-facing content: cs, da, de, el, en, es, fi, fr, hr, hu, is, it, lv, nl, no, pl, pt, ro, sl, sv.
- All user-facing translation files (including why_us.php and gdpr.php) must display the application name as **Paid!** in all supported languages.
- Any future translations must follow this convention.
- The "Why Us" page title is standardized as "Why choose **Paid!**?" and must be translated idiomatically for each language, retaining the bold formatting for "Paid!".
- The comparison table on the "Why Us" page must reference **Paid!** in all languages.
- All translation files are located under `/translations/<lang>/why_us.php`.
- All changes to translation content must be reflected in every supported language.
- No translation files or updates for unsupported languages (bg, et, ga, lt, mt, sk).

> All references to "QR Transfer" or "iwantto.be" have been replaced with **Paid!** throughout the application and translation files. Only supported languages are included in the translation system.

## Translation Branding Consistency (2025-04-18)

- The application name is now consistently shown as **Paid!** in all translation files (about, home, menu, support) for supported languages.
- No translation files or updates for unsupported languages (bg, et, ga, lt, mt, sk).

## Branding Update

All functional specifications and user-facing references now consistently use **Paid!** as the application name across all supported languages and translation files.

## 6. Security Requirements

### 6.1 Data Protection
- No sensitive data stored in QR codes
- Essential cookies only
- GDPR compliance
- Third-party services (GoQR) used for QR generation
- Data sent to GoQR's API for QR code generation
- GoQR servers located within the European Union
- GoQR does not store QR code content

### 6.2 Input Validation
- All form inputs validated
- XSS protection
- CSRF protection

## 7. Testing Requirements

### 7.1 Form Validation
- All input fields must be tested
- Validation messages must be verified
- Error states must be tested

### 7.2 Button Layout
- Buttons must stay in single line on all screen sizes
- Button width distribution must be equal
- Spacing must be consistent

### 7.3 Responsive Design
- Test on desktop
- Test on Android
- Test on iPhone

## Repository Rename

- As of April 18, 2025, the GitHub repository for this project is: https://github.com/xdubois-57/iwanttobe.be-paid

Last updated: 2025-04-18
