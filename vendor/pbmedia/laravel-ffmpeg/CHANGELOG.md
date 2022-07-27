# Changelog

All Notable changes to `pbmedia/laravel-ffmpeg` will be documented in this file

## 7.3.0 - 2020-10-??

### Added

-   Built-in support for watermarks.

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

## 7.2.0 - 2020-09-17

### Added

-   Support for inputs from the web

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

## 7.1.0 - 2020-09-04

### Added

-   Support for Laravel 8.0

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

## 7.0.5 - 2020-07-04

### Added

-   Added `CopyFormat` to export a file without transcoding.

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

## 7.0.4 - 2020-06-03

### Added

-   Added an `each` method to the `MediaOpener`

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

## 7.0.3 - 2020-06-01

### Added

-   Added a `MediaOpenerFactory` to support pre v7.0 facade

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

## 7.0.2 - 2020-06-01

### Added

-   Nothing

### Deprecated

-   Nothing

### Fixed

-   Audio bugfix for HLS exports with filters

### Removed

-   Nothing


## 7.0.1 - 2020-05-28

### Added

-   Nothing

### Deprecated

-   Nothing

### Fixed

-   Fixed HLS playlist creation on Windows hosts

### Removed

-   Nothing

## 7.0.0 - 2020-05-26

### Added

-   Support for both Laravel 6.0 and Laravel 7.0
-   Support for multiple inputs/outputs including mapping and complex filters
-   Concatenation with transcoding
-   Concatenation without transcoding
-   Support for image sequences (timelapse)
-   Bitrate, framerate and resolution data in HLS playlist
-   Execute one job for HLS export instead of one job for each format
-   Custom playlist/segment naming pattern for HLS export
-   Support for disabling log

### Deprecated

-   Nothing

### Fixed

-   Improved progress monitoring
-   Improved handling of remote filesystems

### Removed

-   Nothing

## 6.0.0 - 2020-03-03

### Added

-   Support for Laravel 7.0

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Support for Laravel 6.0

## 5.0.0 - 2019-09-03

### Added

-   Support for Laravel 6.0

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Support for PHP 7.1
-   Support for Laravel 5.8

### Security

-   Nothing

## 4.1.0 - 2019-08-28

### Added

-   Nothing

### Deprecated

-   Nothing

### Fixed

-   Lower memory usage when opening remote files

### Removed

-   Nothing

### Security

-   Nothing

## 4.0.1 - 2019-06-17

### Added

-   Nothing

### Deprecated

-   Nothing

### Fixed

-   Support for php-ffmpeg 0.14

### Removed

-   Nothing

### Security

-   Nothing

## 4.0.0 - 2019-02-26

### Added

-   Support for Laravel 5.8.
-   Support for PHP 7.3.

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

### Security

-   Nothing

## 3.0.0 - 2018-09-03

### Added

-   Support for Laravel 5.7.

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

### Security

-   Nothing

## 2.1.0 - 2018-04-10

### Added

-   Option to disable format sorting in HLS exporter.

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

### Security

-   Nothing

## 2.0.1 - 2018-02-30

### Added

-   Nothing

### Deprecated

-   Nothing

### Fixed

-   Symfony 4.0 workaround

### Removed

-   Nothing

### Security

-   Nothing

## 2.0.0 - 2018-02-19

### Added

-   Support for Laravel 5.6.

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Support for Laravel 5.5 and earlier.

### Security

-   Nothing

## 1.3.0 - 2017-11-13

### Added

-   Support for monitoring the progress of a HLS Export.

### Deprecated

-   Nothing

### Fixed

-   Some refactoring

### Removed

-   Nothing

### Security

-   Nothing

## 1.2.0 - 2017-11-13

### Added

-   Support for adding filters per format in the `HLSPlaylistExporter` class by giving access to the `Media` object through a callback.

### Deprecated

-   Nothing

### Fixed

-   Some refactoring

### Removed

-   Nothing

### Security

-   Nothing

## 1.1.12 - 2017-09-05

### Added

-   Support for Package Discovery in Laravel 5.5.

### Deprecated

-   Nothing

### Fixed

-   Some refactoring

### Removed

-   Nothing

### Security

-   Nothing

## 1.1.11 - 2017-08-31

### Added

-   Added `withVisibility` method to the MediaExporter

### Deprecated

-   Nothing

### Fixed

-   Some refactoring

### Removed

-   Nothing

### Security

-   Nothing

## 1.1.10 - 2017-08-16

### Added

-   Added `getFirstStream()` method to the `Media` class

### Deprecated

-   Nothing

### Fixed

-   Some refactoring

### Removed

-   Nothing

### Security

-   Nothing

## 1.1.9 - 2017-07-10

### Added

-   Support for custom filters in the `Media` class

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

### Security

-   Nothing

## 1.1.8 - 2017-05-22

### Added

-   `getDurationInMiliseconds` method in Media class

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

### Security

-   Nothing

## 1.1.7 - 2017-05-22

### Added

-   `fromFilesystem` method in FFMpeg class

### Deprecated

-   Nothing

### Fixed

-   Fallback to format properties in `getDurationInSeconds` method (Media class)

### Removed

-   Nothing

### Security

-   Nothing

## 1.1.6 - 2017-05-11

### Added

-   `cleanupTemporaryFiles` method

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

### Security

-   Nothing

## 1.1.5 - 2017-03-20

### Added

-   Nothing

### Deprecated

-   Nothing

### Fixed

-   Bugfix for saving on remote disks

### Removed

-   Nothing

### Security

-   Nothing

## 1.1.4 - 2017-01-29

### Added

-   Nothing

### Deprecated

-   Nothing

### Fixed

-   Support for php-ffmpeg 0.8.0

### Removed

-   Nothing

### Security

-   Nothing

## 1.1.3 - 2017-01-05

### Added

-   Nothing

### Deprecated

-   Nothing

### Fixed

-   HLS segment playlists output path is now relative

### Removed

-   Nothing

### Security

-   Nothing

## 1.1.2 - 2017-01-05

### Added

-   Added 'getDurationInSeconds' method to Media class.

### Deprecated

-   Nothing

### Fixed

-   Nothing

### Removed

-   Nothing

### Security

-   Nothing
