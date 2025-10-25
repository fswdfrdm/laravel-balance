<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## API Приложение для работы с балансом пользователей (PHP / Laravel)

### В проекте реализовано:

- Зачисление средств
- Списывание средств
- Перевод средств другим пользователям
- Получение текущего баланса
- Все операции реализованы в транзакциях
- Все ответы приходят в JSON формате, с HTTP кодами (200/404/409/422)

### Стэк проекта:

- php-fpm 8.3 (nginx)
- postgresql 16
- laravel 12

### Установка в docker среде:

- git clone https://github.com/fswdfrdm/laravel-balance.git
- cp .env.example .env (в папке проекта /src/)
- docker-compose up -d --build
- docker-compose exec app php artisan migrate
- docker-compose exec app php artisan db:seed

<pre>
Проект развернут для тестирования, можно переходить к http запросам
</pre>

## Пример http запросов (с curl):

### Баланс пользователя:

<pre>
Можно посмотреть обычным GET запросом в браузере
</pre>

```
http://localhost:8000/api/balance/1
```

### Пополнение баланса пользователя:

```
curl -X POST http://host.docker.internal:8000/api/deposit \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "amount": 50.25,
    "comment": "Пополнение через карту"
  }'
```

### Списание средств с баланса пользователя:

```
curl -X POST http://host.docker.internal:8000/api/withdraw \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "amount": 50.25,
    "comment": "Покупка"
  }'
```

### Перевод средств другому пользователю:

```
curl -X POST  http://host.docker.internal:8000/api/transfer \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "to_user_id": 2,
    "amount": 500.00,
    "comment": "Перевод на карту"
  }'
```

## Структура проекта

<pre>
src/
├── app/
│   ├── Http/
│   │   ├── Controller/
│   │   │   └── BalanceController.php           # Основной контроллер API
│   │   └── Request/
│   │       └── BalanceRequest.php              # Валидация
│   ├── Models/
│   │   ├── Balance.php                         # Модель баланса пользователей
│   │   └── Transaction.php                     # Модель транзакций
│   └── Services/
│       └── BalanceService.php                  # Вся бизнес-логика контроллера BalanceController
└── routes/                                 
    └── api.php                                 # Роутинг приложения
</pre>
