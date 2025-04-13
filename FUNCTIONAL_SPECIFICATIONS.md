# QR Transfer - Functional Specifications

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

### 5.1 Supported Languages
- English (en)
- French (fr)
- Dutch (nl)
- Latvian (lv)

### 5.2 Translation Files
- Located in /translations directory
- PHP array format
- All user-facing text must be translated

## 6. Security Requirements

### 6.1 Data Protection
- No sensitive data stored in QR codes
- Essential cookies only
- GDPR compliance

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

Last updated: 2025-04-11
