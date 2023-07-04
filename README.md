# Tax Residency Assistant

[![build](https://github.com/tbikbulatov/trasst/actions/workflows/build.yml/badge.svg)](https://github.com/tbikbulatov/trasst/actions/workflows/build.yml)
[![test: status](https://github.com/tbikbulatov/trasst/actions/workflows/test.yml/badge.svg)](https://github.com/tbikbulatov/trasst/actions/workflows/test.yml)

## About

Tax Residency Assistant (TRAsst) is a project designed to help determine a tourist's tax residency by analyzing their stay journal. The primary objective of this project is to implement best architectural practices. TRAsst operates as a self-contained system and does not rely on external services.

## Getting started

```shell
$ cd trasst && make install
```
Open https://localhost/api in web browser and accept the auto-generated TLS certificate.

A list of available commands can be provided by running `make` in the project directory. 

## Next features

The following features are planned for future development in TRAsst:

* Implement specific handling of stay purposes. For example, stays for medical treatment or civil service should not increase the days counter.
* Add an estimation of a tourist's potential tax residency based on the analysis outcome.
* Introduce rule conditions that need to be evaluated before running the main checks. For instance, certain rules may have specific logic that should be verified after a particular year.
* Enhance the input Journal to include non-personalized data about the tourist, such as:
  * List of the tourist's citizenship countries
  * Current tax residency countries of the tourist
  * Country of workplace for the tourist
  * Countries where the tourist owns real estate
  * Primary country of family living for the tourist
  * Official income of the tourist
  * Tourist's fortune

  (this expansion is necessary to implement more sophisticated rules, such as checks for financial interests, center of vital interests, and other complex scenarios)
* Develop a tax residency optimizer to provide recommendations for the best next stay options. For example, suggesting moving to a country that allows for avoiding double taxation.
* Create an admin context to enable managers to configure country tax residency rules.

## Would be nice to implement

* Domain & integration events
* Auto (implicit) DB transactions for `CommandHandler`s

## Known design issues

* A mapping overhead: Domain\Entity → App\Result → Infra\ApiPlatform\Resource
* Direct domain exceptions mapping to HTTP codes
* PHP's `\DateTime*` dependencies in the Domain layer (possibly should be replaced by ValueObjects and Infrastructure service to operate them)
* API Platform's [issue](https://github.com/api-platform/api-platform/issues/788) with deserialized object validation, instead of Request validation  

## License

Tax Residency Assistant is available under the MIT License.
