# Changelog

## [4.0.0](https://github.com/ankurk91/laravel-ses-webhooks/compare/3.1.2...4.0.0)

* Model prune feature has been removed from `SesWebhookCall` class
* Follow the [upgrade guide](./UPGRADING.md) to restore this feature

## [3.1.2](https://github.com/ankurk91/laravel-ses-webhooks/compare/3.1.1...3.1.2)

* Fix compatibility with `spatie/laravel-webhook-client`, see
  this [PR](https://github.com/spatie/laravel-webhook-client/pull/166)

## [3.1.0](https://github.com/ankurk91/laravel-ses-webhooks/compare/3.0.0...3.1.0)

* Drop support for Laravel 8
* Drop support for php 8.0
* Test on php 8.2

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
