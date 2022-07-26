# Changelog

## [3.0.0](https://github.com/ankurk91/laravel-ses-webhooks/compare/2.0.0...3.0.0)

* :warning: Changed namespace for various classes
* Don't save full url to database, rather just save the path
* Mark Job as failed immediately when invalid class mapped with event type

## [2.0.0](https://github.com/ankurk91/laravel-ses-webhooks/compare/1.1.0...2.0.0)

* Decode and store `Message` with payload, see [Upgrade guide](./UPGRADING.md)

## [1.1.0](https://github.com/ankurk91/laravel-ses-webhooks/compare/1.0.0...1.1.0)

* Allow pruning webhooks via scheduling

## 1.0.0

* Initial release
