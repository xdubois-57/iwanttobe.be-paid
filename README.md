# iwantto.be Paid

A web application for generating QR codes for SEPA bank transfers, following the EPC069-12 standard.

## Features

- 🏦 Generate QR codes for SEPA bank transfers
- 🌍 Multilingual support (20+ languages)
- 💾 Form data persistence with favorites
- 📱 Responsive design for desktop and mobile
- ✅ Real-time form validation
- 🔒 GDPR compliant
- 📱 Built-in QR code scanner
- 📊 Word cloud visualization of payment trends

## Technical Stack

- PHP (MVC Architecture)
- JavaScript (Form validation and AJAX)
- [PicoCSS](https://picocss.com/) for minimal, semantic HTML/CSS
- LocalStorage for form data persistence
- Session-based language management
- Composer for dependency management
- chillerlan/php-qrcode for QR code generation

## Dependencies

This project requires the [chillerlan/php-qrcode](https://github.com/chillerlan/php-qrcode) library for QR code generation. Install it using Composer:

```bash
composer require chillerlan/php-qrcode
```

If you do not have Composer installed, see https://getcomposer.org/

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
- `translations/` - Language files (global and app-specific)
- `config/` - Configuration files
- `apps/` - Application modules (Paid!, Drive, etc.)

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the GNU General Public License v3.0 (GPLv3). See the [LICENSE](LICENSE) file for details.

## Author

Xavier Dubois - [admin@iwantto.be](mailto:admin@iwantto.be)

## Project Structure

The project follows a modular architecture with multiple applications:

- `paid/` - Main SEPA transfer QR code generator
- `drive/` - Document management system
- `involved/` - Community engagement platform
- `register_apps.php` - Application registration system
