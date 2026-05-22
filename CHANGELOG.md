# Changelog

All notable changes to `codebros/dutch-holidays` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-05-22

### Added
- Initial release.
- Calculation of all 11 Dutch national holidays via Gauss algorithm.
- 10 pre-defined sector profiles: default, government, construction, metal, healthcare, education, hospitality, ict, grafimedia, retail.
- `HolidayPolicy` enum (Official / Always / Never) with per-holiday override.
- `SectorProfile` value object with `withPolicies()` and `withExtraHolidays()`.
- `HolidayProvider` contract for extension via custom sources.
- Workday helpers: `isWorkday`, `nextWorkday`, `previousWorkday`, `workdaysBetween`, `addWorkdays`.
- Multi-year and date-range retrieval: `forYears`, `between`.
- Carbon macros: `isDutchHoliday`, `isDutchWorkday`, `nextDutchWorkday`, `previousDutchWorkday`, `addDutchWorkdays`.
- Artisan commands: `holidays:list`, `holidays:sectors`.
- Full Pest test suite with 50+ assertions.
