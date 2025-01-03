## Rotas

Registrar um Usuário (POST /api/register)

curl -X POST http://localhost/api/register \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"name": "John Doe", "email": "johndoe@example.com", "password": "secret12"}'


Fazer Login e Obter um Token (POST /api/login)

curl -X POST http://localhost/api/login \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"email": "johndoe@example.com", "password": "secret12"}'

Fazer uma Requisição Protegida (GET /api/user)

curl -X GET http://localhost/api/user \
     -H "Authorization: Bearer {token}" \
     -H "Accept: application/json"
