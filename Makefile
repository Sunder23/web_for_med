COMPOSE = docker compose -f docker/compose.yml

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

logs:
	$(COMPOSE) logs -f wordpress

shell:
	$(COMPOSE) exec wordpress bash

db-shell:
	$(COMPOSE) exec db sh -c 'mysql -uroot -p"$$MYSQL_ROOT_PASSWORD" "$$MYSQL_DATABASE"'

fresh:
	$(COMPOSE) down -v
	$(COMPOSE) up -d

# Usage: make wp cmd="plugin list"
wp:
	$(COMPOSE) run --rm wpcli wp $(cmd)

import:
	$(COMPOSE) run --rm wpcli wp eval-file /scripts/import/import-services.php
	$(COMPOSE) run --rm wpcli wp eval-file /scripts/import/import-directions.php
	$(COMPOSE) run --rm wpcli wp eval-file /scripts/import/import-cases.php
	$(COMPOSE) run --rm wpcli wp eval-file /scripts/import/import-posts.php
	$(COMPOSE) run --rm wpcli wp eval-file /scripts/import/setup-menu.php
	$(COMPOSE) run --rm wpcli wp rewrite flush --hard

# One-time front page -> section blocks migration (dry run by default).
# Usage: make migrate-front-page args="apply cleanup"
migrate-front-page:
	$(COMPOSE) run --rm wpcli wp eval-file /scripts/migrate-front-page-to-blocks.php $(args)
