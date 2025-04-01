# QR Transfer

A web application for generating QR codes for SEPA bank transfers, following the EPC069-12 standard.

## Features

- 🏦 Generate QR codes for SEPA bank transfers
- 🌍 Multilingual support (EN, FR, NL)
- 💾 Form data persistence
- 📱 Responsive design for desktop and mobile
- ✅ Real-time form validation
- 🔒 GDPR compliant

## Technical Stack

- PHP (MVC Architecture)
- JavaScript (Form validation and AJAX)
- [PicoCSS](https://picocss.com/) for minimal, semantic HTML/CSS
- LocalStorage for form data persistence
- Session-based language management

## Installation

1. Clone the repository:
```bash
git clone https://github.com/xdubois-57/qrtransfer.git
```

2. Set up your web server (Apache/Nginx) to point to the project directory

3. Ensure the following PHP extensions are enabled:
   - session
   - json
   - mbstring

## Usage

1. Visit the homepage
2. Fill in the transfer details:
   - Beneficiary name
   - IBAN number
   - Amount
   - Communication (optional)
3. Click "Generate QR" to create your QR code
4. Scan the QR code with your banking app

## Development

- `controllers/` - MVC Controllers
- `views/` - PHP view templates
- `js/` - JavaScript files for client-side functionality
- `translations/` - Language files
- `config/` - Configuration files

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Author

Xavier Dubois - [xdubois@gmail.com](mailto:xdubois@gmail.com)
