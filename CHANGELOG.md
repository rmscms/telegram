# Changelog

All notable changes to this project will be documented in this file.

## [1.2.0] - 2025-10-20

### Fixed
- **Critical**: Fixed case-sensitivity issue with `Keyboard.php` file for Linux compatibility
  - Renamed `KeyBoard.php` to `Keyboard.php` to ensure proper autoloading on Linux systems
  - This resolves `Class "RMS\Telegram\Keyboard" not found` errors in production

### Changed
- Improved package stability for multi-platform deployments

### Dependencies
- No changes to dependencies

## [1.1.0] - 2025-10-20

### Added
- Initial release with Telegram integration for Laravel
- Support for inline and reply keyboards
- Media group support
- Helper functions for easy integration
- Service provider for seamless Laravel integration

### Features
- Send text messages
- Send photos and media files
- Manage keyboards (inline & reply)
- Button creation and management
- Full Telegram Bot API v3.15+ support
