# Tax Residency Assistant (TRAsst)

[![build](https://github.com/tbikbulatov/trasst/actions/workflows/build.yml/badge.svg)](https://github.com/tbikbulatov/trasst/actions/workflows/build.yml)
[![test: status](https://github.com/tbikbulatov/trasst/actions/workflows/test.yml/badge.svg)](https://github.com/tbikbulatov/trasst/actions/workflows/test.yml)

## About

This project is an example Symfony/API Platform app that demonstrates several architectural approaches, such as layered architecture and CQRS.

## Domain

The core domain of this application revolves around determining a person's **tax residency** status by analyzing their international travel history:

- **Journal**: A collection of stays representing a person's complete travel history
- **Stay**: A single visit to a country, defined by country code, purpose, start date, and end date
- **Tax Residency Policy**: Country-specific rules that determine tax residency status
- **Analysis**: The process of applying tax residency policies to a journal to determine residency status

Different countries have vastly different tax residency rules. For example:
- **Armenia**: Simple 183-day rule (resident if present for 183+ days in a calendar year)
- **United States**: Complex "substantial presence test" requiring:
  - At least 31 days in the current year, AND
  - At least 183 days during a 3-year period, counting all days in the current year, 1/3 of days in the previous year, and 1/6 of days in the year before that

The application implements these country-specific rules as policies, allowing for accurate tax residency determination across multiple jurisdictions.


## Getting started

```shell
$ cd trasst && make install && make start
```
Open https://localhost/api in web browser and accept the auto-generated TLS certificate.

A list of available commands can be provided by running `make` in the project directory. 

## Future features

The following features are planned for future development:

* **Implement MCP-server**
* **Specific Handling of Stay Purposes**:
Implement logic for different stay purposes (e.g., medical treatment or civil service stays should not count towards the day counter)
* **Tax Residency Estimation**:
Add an estimation of a tourist's potential tax residency based on the analysis outcome
* **Conditional Rule Evaluation**:
Introduce rule conditions that need to be evaluated before running the main checks. For example, certain rules may have specific logic that should only be verified after a particular year
* **Enhanced Input Journal**:
Expand the input journal to include non-personalized data about the tourist, such as:
  * List of the tourist's countries of citizenship
  * Current tax residency countries
  * Country of workplace
  * Countries where the tourist owns real estate
  * Primary country of family living
  * Official income
  * Tourist's net worth (this expansion is necessary to implement more sophisticated rules, such as checks for financial interests, center of vital interests, and other complex scenarios)
* **Tax Residency Optimizer**:
Develop a tax residency optimizer to provide recommendations for optimal future stay options (e.g., suggest moving to another country to avoid double taxation)
* **Admin Context**:
Create an admin interface to enable managers to configure country tax residency rules.

### Improvements
* Implement domain and integration events.
* Automate (implicit) DB transactions for CommandHandlers.

## Known design issues

* A mapping overhead: `Domain\Entity` → `App\Result` → `Infra\ApiPlatform\Resource`
* Direct domain exceptions mapping to HTTP codes
* API Platform's [issue](https://github.com/api-platform/api-platform/issues/788) with deserialized object validation, instead of Request validation  

## License

Tax Residency Assistant is available under the MIT License.
