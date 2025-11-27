setup:
	@echo "Clear databases..."
	@docker exec -it postgres psql -U postgres -d postgres -c "DROP DATABASE IF EXISTS ina_zaoui WITH (FORCE);"
	@docker exec -it postgres psql -U postgres -d postgres -c "CREATE DATABASE ina_zaoui;"
	@docker exec -it postgres psql -U postgres -d postgres -c "DROP DATABASE IF EXISTS ina_zaoui_test WITH (FORCE);"
	@docker exec -it postgres psql -U postgres -d postgres -c "CREATE DATABASE ina_zaoui_test;"

	@docker exec -it app php bin/console doctrine:schema:update --force
	@#docker exec -it app php bin/console doctrine:migrations:migrate --no-interaction

	# Faire les backups
	@docker exec -i postgres psql -U postgres -d ina_zaoui < dump.sql

backup:
	@docker exec -it postgres pg_dump -U postgres -d ina_zaoui -F c -b -v -f /var/lib/postgresql/data/backup/ina_zaoui.backup

# Soon:
test:
	@composer db-test
	@vendor/bin/phpunit
	@php -d memory_limit=1G vendor/bin/phpstan analyse

stan:
	@php vendor/bin/phpstan clear-result-cache
	@php -d memory_limit=1G vendor/bin/phpstan analyse