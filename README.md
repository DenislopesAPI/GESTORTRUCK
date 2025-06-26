# Gestor Truck

Esta aplicação utiliza credenciais de banco de dados e SMTP obtidas por variáveis de ambiente. Para configurar o ambiente local:

1. Copie o arquivo `.env.example` para `.env`.
2. Edite os valores conforme suas credenciais de banco de dados e servidor de e-mail.
3. Certifique-se de que o PHP possa ler estas variáveis (por exemplo carregando-as com um gerenciador de ambiente ou exportando-as antes de iniciar o servidor).

O arquivo `config.php` fará a leitura dessas variáveis e disponibilizará as configurações para o restante do código.

## Testar envio SMTP localmente

Um script de exemplo está disponível em `examples/teste_smtp.php` para verificar se as configurações de e-mail estão corretas.

1. Preencha as variáveis relacionadas ao SMTP no arquivo `.env`.
2. A partir da raiz do projeto, execute:

```bash
php examples/teste_smtp.php
```

Se tudo estiver configurado corretamente, será exibida uma mensagem indicando que o e-mail foi enviado.
