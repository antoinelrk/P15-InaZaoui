setup:
	@echo "Clear databases..."
	@docker exec -it ina_zaoui_postgres psql -U postgres -d postgres -c "DROP DATABASE IF EXISTS ina_zaoui WITH (FORCE);"
	@docker exec -it ina_zaoui_postgres psql -U postgres -d postgres -c "CREATE DATABASE ina_zaoui;"
	@docker exec -it ina_zaoui_postgres psql -U postgres -d postgres -c "DROP DATABASE IF EXISTS ina_zaoui_test WITH (FORCE);"
	@docker exec -it ina_zaoui_postgres psql -U postgres -d postgres -c "CREATE DATABASE ina_zaoui_test;"

	@docker exec -it ina_zaoui_app php bin/console doctrine:schema:update --force

	$(MAKE) fixtures-load
fixtures-load:
	@echo "Loading fixtures..."
	@docker exec -it ina_zaoui_app php bin/console doctrine:fixtures:load --no-interaction

dump:
	@docker exec -i ina_zaoui_postgres psql -U postgres -d ina_zaoui < dump.sql

clean:
	@echo "Cleaning project..."
	@rm -rf var/cache/*
	@rm -rf var/log/*
	@rm -rf var/sessions/*
	@docker exec -it ina_zaoui_app php bin/console cache:clear
	@php bin/console cache:clear


# Soon:
test:
	@composer db-test
	@vendor/bin/phpunit
	@php -d memory_limit=1G vendor/bin/phpstan analyse

stan:
	@php vendor/bin/phpstan clear-result-cache
	@php -d memory_limit=1G vendor/bin/phpstan analyse
