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
