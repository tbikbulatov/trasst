.DEFAULT_GOAL := help

DC = docker compose
EXEC = $(DC) exec php
COMPOSER = $(EXEC) composer

ifndef CI_JOB_ID
	GREEN  := $(shell tput -Txterm setaf 2)
	YELLOW := $(shell tput -Txterm setaf 3)
	RESET  := $(shell tput -Txterm sgr0)
	TARGET_MAX_CHAR_NUM=30
endif

help:
	@echo "Tax Residency Assistant"
	@awk '/^[a-zA-Z\-\_0-9]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")-1); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf "  ${GREEN}%-$(TARGET_MAX_CHAR_NUM)s${RESET} %s\n", helpCommand, helpMessage; \
		} \
		isTopic = match(lastLine, /^###/); \
	    if (isTopic) { \
			topic = substr($$1, 0, index($$1, ":")-1); \
			printf "\n${YELLOW}%s${RESET}\n", topic; \
		} \
	} { lastLine = $$0 }' $(MAKEFILE_LIST)



#################################
Project:

## Enter the application container
php:
	@$(EXEC) sh

## Install the whole dev environment
install:
	@$(DC) build
	@$(MAKE) start -s
	@$(MAKE) vendor -s

## Install composer dependencies
vendor:
	@$(COMPOSER) install --optimize-autoloader

## Start the project
up:
	@$(DC) up -d --remove-orphans --no-recreate

## Stop the project
down:
	@$(DC) stop
	@$(DC) rm -v --force

.PHONY: php install vendor up down




#################################
Tests:

## Run codestyle static analysis
cs:
	@$(DC) exec -e PHP_CS_FIXER_IGNORE_ENV=1 php vendor/bin/php-cs-fixer fix --dry-run --diff

## Run psalm static analysis
psalm:
	@$(EXEC) vendor/bin/psalm --show-info=true

## Run code depedencies static analysis
deptrac:
	#@echo "\n${YELLOW}Checking Bounded contexts...${RESET}"
	@$(EXEC) vendor/bin/deptrac analyze -c deptrac_bc.yaml --cache-file=.deptrac_bc.cache -n --no-progress --fail-on-uncovered --report-uncovered

	#@echo "\n${YELLOW}Checking Hexagonal layers...${RESET}"
	@$(EXEC) vendor/bin/deptrac analyze -c deptrac_layers.yaml --cache-file=.deptrac_layers.cache -n --no-progress --fail-on-uncovered --report-uncovered

## Run all phpunit tests
tests:
	@$(EXEC) vendor/phpunit/phpunit/phpunit --testsuite=All

## Run only unit tests
unit-tests:
	@$(EXEC) vendor/phpunit/phpunit/phpunit --testsuite=Unit

## Run only API tests
api-tests:
	@$(EXEC) vendor/phpunit/phpunit/phpunit --testsuite=Api

## Run either static analysis and tests
ci: cs psalm deptrac tests

.PHONY: cs psalm deptrac tests unit-tests api-tests ci




#################################
Tools:

## Fix PHP files to be compliant with coding standards
fix-cs:
	@$(DC) exec -e PHP_CS_FIXER_IGNORE_ENV=1 php vendor/bin/php-cs-fixer fix

.PHONY: fix-cs
