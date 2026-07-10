up:
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f wordpress

shell:
	docker compose exec wordpress bash

db-shell:
	docker compose exec db mysql -u${WORDPRESS_DB_USER} -p${WORDPRESS_DB_PASSWORD} ${WORDPRESS_DB_NAME}

fresh:
	docker compose down -v
	docker compose up -d

# Usage: make wp cmd="plugin list"
wp:
	docker compose run --rm cli wp $(cmd)

import:
	docker compose run --rm cli wp eval-file /scripts/import/import-services.php
	docker compose run --rm cli wp eval-file /scripts/import/import-directions.php
	docker compose run --rm cli wp eval-file /scripts/import/import-cases.php
	docker compose run --rm cli wp eval-file /scripts/import/import-posts.php
	docker compose run --rm cli wp eval-file /scripts/import/setup-menu.php
	docker compose run --rm cli wp rewrite flush --hard
