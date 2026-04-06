# 🚀 Laravel 10 + Docker + Sail (sin PHP local)

## 🧠 Requisitos

- Docker instalado
- WSL funcionando (Linux recomendado)
- Trabajar en `/home`, no en `/mnt/c`

```bash
cd ~
mkdir -p projects
cd projects
```

---

## 🏗️ 1. Crear proyecto Laravel 10

```bash
docker run --rm \
-u $(id -u):$(id -g) \
-v ${PWD}:/opt \
-w /opt \
laravelsail/php82-composer:latest \
composer create-project laravel/laravel:^10.0 laravel-auth-pakamuros
```

---

## 📂 2. Entrar al proyecto

```bash
cd laravel-auth-pakamuros
```

---

## ⚡ 3. Instalar Laravel Sail

```bash
docker run --rm \
-u $(id -u):$(id -g) \
-v ${PWD}:/opt \
-w /opt \
laravelsail/php82-composer:latest \
composer require laravel/sail --dev
```

---

## 🐳 4. Inicializar Sail

```bash
docker run --rm \
-u $(id -u):$(id -g) \
-v ${PWD}:/opt \
-w /opt \
laravelsail/php82-composer:latest \
php artisan sail:install --with=none
```

---

## 🚀 5. Levantar contenedores

```bash
./vendor/bin/sail up -d
```

---

## 💡 Alias opcional

```bash
echo "alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'" >> ~/.zshrc
source ~/.zshrc
```

---

## 🧪 Comandos útiles

```bash
sail artisan migrate
sail artisan make:model User
sail composer require paquete
```

---

## 🌐 Acceso

http://localhost
