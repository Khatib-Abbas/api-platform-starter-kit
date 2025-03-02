# Index des documents

- [docker-cache-build](docs/docker-cache-build.md)
- [docker-h](docs/docker-ha.md)
- [secret-file-env](docs/secret-file-env.md)
- [traefik-caddy-php](docs/traefik-caddy-php.md)

---

# 1. Install certs


```bash

chmod +x openssl_certs.sh && ./openssl_certs.sh

```
---

# 2. Install deps (optional)

```bash

chmod +x update-deps.sh ./update-deps.sh

```

---

---

# 3. Set the Secrets

https://symfony.com/doc/current/configuration/env_var_processors.html

```yaml
#compose.yaml
services:
  php:
	#...
    secrets:
      - api_key
secrets:
  api_key:
    file: secrets/api_key.txt
```

In Symfony

```yaml
#api/config/services.yaml
parameters:
    app.api_key: '%env(file:API_KEY_FILE)%'
```

Call the env in php

```php
 $this->getParameter('app.api_key')
```


---

# 4. Run Container

```bash

docker compose up -d --remove-orphans

```
