Installation
------------

```bash
docker-compose up -d
symfony console doctrine:schema:update --force
symfony console doctrine:fixtures:load -n -vvv
```
