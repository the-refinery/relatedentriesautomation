# relatedentriesautomation Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 0.3.10 - 2020-04-01
### Changed
- Date filtering fields: changed wording and ordering of inputs to more clearly communicate how field works

## 0.3.9 - 2020-04-01
### Fixed
- POSTDate value field would not update unless user clicked out of field

## 0.3.8 - 2020-01-29
### Fixed
- `filterEntries` more WHERE clasues to filter out drafts. Must filter on `DraftId` and `revisionId` fields

## 0.3.7 - 2019-12-02
### Fixed
- Added WHERE clause to `filterEntries` function to filter out drafts

## 0.2.9 - 2019-02-05
### Added
- Swap sql groupby with distinct for much better performance

## 0.2.7 - 2018-10-15
### Added
- Meta data updates

## 0.2.0 - 2018-06-21
### Added
- Updates for Craft CMS 3

## 0.1.0 - 2017-04-06
### Added
- Initial release
