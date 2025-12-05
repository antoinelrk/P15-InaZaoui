serve:
	@php -S localhost:8000 -t public

setup:
	@echo "Clear databases..."
	@docker exec -it ina_zaoui_postgres psql -U postgres -d postgres -c "DROP DATABASE IF EXISTS ina_zaoui WITH (FORCE);"
	@docker exec -it ina_zaoui_postgres psql -U postgres -d postgres -c "CREATE DATABASE ina_zaoui;"
	@docker exec -it ina_zaoui_postgres psql -U postgres -d postgres -c "DROP DATABASE IF EXISTS ina_zaoui_test WITH (FORCE);"
	@docker exec -it ina_zaoui_postgres psql -U postgres -d postgres -c "CREATE DATABASE ina_zaoui_test;"

	@php bin/console doctrine:schema:update --force
	@php bin/console doctrine:fixtures:load --no-interaction

	@php bin/console doctrine:schema:update --force --env=test
	@php bin/console doctrine:fixtures:load --no-interaction --env=test
dump:
	@docker exec -i ina_zaoui_postgres psql -U postgres -d ina_zaoui < dump.sql

clean:
	@echo "Cleaning project..."
	@rm -rf var/cache/*
	@rm -rf var/log/*
	@rm -rf var/sessions/*
	@php bin/console cache:clear

test:
	@vendor/bin/phpunit --coverage-html=coverage

stan:
	@php vendor/bin/phpstan clear-result-cache
	@php -d memory_limit=1G vendor/bin/phpstan analyse
