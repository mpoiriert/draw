DOMAIN := draw.com

include make/shared.mk

tester-dump-assert-methods:
	docker-compose exec php bin/console draw:tester:dump-assert-methods ./src/Component/Tester/Resources/config/assert_methods.json

tester-generate-trait:
	docker-compose exec php bin/console draw:tester:generate-trait

tester-generate-doc:
	docker-compose exec php bin/console draw:tester:generate-asserts-documentation-page

tester-generate-all: tester-dump-assert-methods tester-generate-trait tester-generate-doc

monorepo-merge:
	docker-compose exec php vendor-bin/monorepo/vendor/bin/monorepo-builder merge

monorepo-release:
	DRY_RUN=--dry-run
ifeq ($(run),1)
	DRY_RUN=
endif
	docker-compose exec php vendor-bin/monorepo/vendor/bin/monorepo-builder release $(version) $$DRY_RUN

monorepo-release-patch:
	docker-compose exec php vendor-bin/monorepo/vendor/bin/monorepo-builder release patch

composer-normalize:
	[ -f ./composer-normalize] && echo 'composer-normalizer available' || wget https://github.com/ergebnis/composer-normalize/releases/download/2.24.1/composer-normalize.phar -O composer-normalize
	sudo chmod a+x composer-normalize
	docker-compose exec php php composer-normalize

generate-artifact:
	docker-compose exec php composer generate:artifact