# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Implemented secure API key storage using encryption with WordPress salts.
- Added a robust API client with retry mechanism and exponential backoff.
- Implemented a circuit breaker pattern to handle repeated API failures gracefully.
- Implemented an asynchronous job queue system for content generation.

### Changed
- Refactored the Gemini API class from `IACP_Gemini_Api` to `IACP_Gemini_Client` for better clarity.
- Content generation is now an asynchronous process to prevent AJAX timeouts.

### Fixed
- Verified the implementation of all features in TASK-201, ensuring they meet the requirements.

### Security
- Added permission checks to all AJAX endpoints.
- Implemented input sanitization for content generation workflows.
- Improved JSON parsing from API responses to be more robust.

For more technical details, see [Technical Log for TASK-201](./technical_log/TASK-201.md), [Technical Log for TASK-202](./technical_log/TASK-202.md), and [Technical Log for TASK-203](./technical_log/TASK-203.md).