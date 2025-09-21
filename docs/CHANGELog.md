# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Implemented secure API key storage using encryption with WordPress salts.
- Added a robust API client with retry mechanism and exponential backoff.
- Implemented a circuit breaker pattern to handle repeated API failures gracefully.

### Changed
- Refactored the Gemini API class from `IACP_Gemini_Api` to `IACP_Gemini_Client` for better clarity.

For more technical details, see [Technical Log for TASK-201](./technical_log/TASK-201.md).
