
BAMARNI_BIN_DIR =: sbin

help:
	@$(MAKE) -s print-help

print-help:
	@awk '/^#/{c=substr($$0,3);next}c&&/^[[:alpha:]][[:alnum:]_-]+:/{print substr($$1,1,index($$1,":")),c}1{c=0}' $(MAKEFILE_LIST) | column -s: -t

.PHONY: _install
_install: clear build cert-generate cert-install provision
	docker-compose exec php composer test

.PHONY: install
# Install the project from scratch and run tests
install: _install

.PHONY: build
# Build docker images
build:
	docker-compose build --pull

.PHONY: provision
# Provision the project, you can add things to do after by creating a provision_after_hook target
provision: up
	docker-compose exec php composer install
	docker-compose exec php composer test:reset
	make provision_after_hook

.PHONY: provision_after_hook
provision_after_hook:

.PHONY: up
# Start docker containers
up:
	docker-compose up -d

.PHONY: down
# Stop docker containers
down:
	docker-compose down --remove-orphans

.PHONY: base_clear
_clear: down
	sudo rm -Rf ./vendor ./var ./public/upload ./node_modules ./public/open-api ./$(BAMARNI_BIN_DIR)/*/vendor
	@if [ -L /usr/local/share/ca-certificates/$(DOMAIN).crt ]; then \
		sudo unlink /usr/local/share/ca-certificates/$(DOMAIN).crt; \
		echo "Certificate unlinked"; \
	else \
		echo "Certificate not found. Nothing to unlink."; \
	fi

.PHONY: clear
clear: _clear

.PHONY: php
# Connect to php container
php:
	docker-compose exec php bash

.PHONY: mysql
# Connect to mysql container
mysql:
	docker-compose exec mysql bash

.PHONY: cert-generate
# Generate self-signed certificate
cert-generate:
	rm -Rf .docker/nginx/certs/$(DOMAIN)*
	docker-compose run --rm nginx openssl req -x509 -nodes -days 365 -subj "/C=CA/ST=QC/O=Company, Inc./CN=$(DOMAIN)" -addext "subjectAltName=DNS.1:$(DOMAIN),DNS.2:*.$(DOMAIN)" -newkey rsa:4098 -keyout /etc/nginx/certs/$(DOMAIN).key -out /etc/nginx/certs/$(DOMAIN).crt;

.PHONY: cert-install
# Install self-signed certificate
cert-install:
	sudo ln -s "$(pwd)/.docker/nginx/certs/$(DOMAIN).crt" /usr/local/share/ca-certificates/$(DOMAIN).crt
	sudo update-ca-certificates

.PHONY: soup
# Run soup
soup:
	docker-compose exec soup soup

.PHONY: migrations-diff
# Generate migration diff
migrations-diff:
	docker-compose exec php bin/console doctrine:migrations:diff --formatted

.PHONY: migrations-migrate
# Migrate database
migrations-migrate:
	docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

.PHONY: test
# Run test
test:
	docker-compose exec php composer test:run

.PHONY: test-coverage
# Run test with coverage
test-coverage:
	docker-compose exec php composer test:coverage

.PHONY: test-reset
# Reset test data
test-reset:
	docker-compose exec php composer test:reset

.PHONY: linter
# Run linter
linter:
	docker-compose exec php composer linter
